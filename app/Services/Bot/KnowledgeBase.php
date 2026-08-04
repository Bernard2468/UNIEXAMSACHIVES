<?php

namespace App\Services\Bot;

use App\Models\BotKnowledgeEntry;
use Illuminate\Support\Facades\Cache;

/**
 * The bot's local "brain".
 *
 * Two jobs:
 *  1. {@see systemMap()} — a complete, human-written description of the UDTS
 *     platform. This is fed to Gemini as the system prompt so the model answers
 *     accurately (the equivalent of GNRS's GNRS_SYSTEM_PROMPT).
 *  2. {@see search()} — a zero-cost retrieval layer. Built-in how-to/FAQ entries
 *     plus Super-Admin-editable {@see BotKnowledgeEntry} rows are scored against
 *     the user's question by token overlap. A confident match is answered
 *     instantly with NO API call — so the bot stays smart even when Gemini is
 *     off, keyless, or rate-limited.
 */
class KnowledgeBase
{
    /** Minimum score (0..1) required to answer from the knowledge base. */
    public const CONFIDENCE_THRESHOLD = 0.34;

    private const STOPWORDS = [
        'a','an','the','is','are','am','do','i','you','how','to','can','what','where','when',
        'my','me','of','in','on','for','and','or','with','it','this','that','please','help',
        'get','go','see','find','need','want','would','should','could','will','be','have','has',
        'about','from','at','as','if','so','we','our','us','system','udts','bot',
    ];

    /** Root-relative URL for a named route, or a safe fallback. */
    private function link(string $routeName, array $params = []): string
    {
        try {
            return route($routeName, $params, false);
        } catch (\Throwable $e) {
            return '/';
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  System map — fed to Gemini as the grounding system prompt
    // ─────────────────────────────────────────────────────────────────────────

    public function systemMap(): string
    {
        return <<<MAP
You are **UDTS Assistant** — the official in-app AI guide for the University Digital Transformation Suite (UDTS)
by Metascholar Consult. You are warm, concise, and you know every corner of this platform in detail. You help
staff and administrators use the system, and you can point them to the exact page they need with a clickable link.

## What UDTS is
UDTS is an internal institutional platform for a university/college. It combines a secure examinations & document
archive, an organisational forms & e-signing workflow, an internal memo/messaging system (UIMMS), and institutional
administration (offices, committees, departments, subscriptions).

## Core areas & where to find them
- **Dashboard** ({$this->link('dashboard')}) — the home page after signing in: quick stats and shortcuts.
- **Exams archive** ({$this->link('dashboard.all.exams')}) — upload, browse and download past exam papers and answer keys. Create via the "+ Create" action.
- **Files / Documents** ({$this->link('dashboard.all.files')}) — the general document archive (upload, organise, download).
- **Folders** ({$this->link('dashboard.folders.index')}) — organise exams & files into folders, share them with departments/committees/offices, and set folder passwords for sensitive material.
- **Forms Portal** ({$this->link('admin.forms.portal')}) — raise and track official forms. A form moves stage-by-stage between offices/leaders, each of whom signs it. Browse form types in the **Forms Gallery** ({$this->link('admin.forms.gallery')}).
- **Memos / UIMMS** ({$this->link('dashboard.uimms.portal')}) — the University Internal Memo & Messaging System: send memos, reply in a chat thread, minute memos to others, and track read status.
- **Notifications** ({$this->link('dashboard.notifications')}) — in-app alerts for memo replies, form assignments and more.
- **Calendar** — events and reminders (opened from the header calendar button).
- **Search** ({$this->link('search.index')}) — global search across the archive (also available via the ⌘K / Ctrl-K command palette).
- **Offices** ({$this->link('offices.index')}) — institutional offices (Finance, Internal Audit, Registrar, VC, Procurement Committee, Director of Finance) that forms route through.
- **Committees** and **Departments** — organisational structure used for sharing and leadership routing.
- **Payment history / Billing** ({$this->link('dashboard.payment-history.index')}) — the institution's subscription invoices and receipts.
- **Profile & Settings** ({$this->link('dashboard.settings')}) — personal details, password, e-signature, memo salutation, and the Appearance tab (per-user text size).
- **System Documentation** ({$this->link('dashboard.system-documentation')}) and **User Manual** ({$this->link('dashboard.user-manual')}) — official guides.

## Forms workflow (important)
- Two families ship today: **Payment Requisition (PR)** and **Purchase/Works Authorization (PWA)**, plus leave forms (Annual, Casual, Resumption), Vehicle Maintenance Allowance, Renewal of Appointment, Employee Personal Records, and Promotion forms.
- A form is composed by a user, then routed through a sequence of **stages**. Each stage is an **office** (e.g. Finance) or a **leadership** role (HOD / Dean / Director). The current assignee signs, and it advances to the next stage.
- Every signature is tamper-evident (hash-chained), so a signed stage can't be altered without breaking later stages.
- To act on a form it must be **in progress** and assigned to you. You can then **sign**, **reject**, **comment**, or **reassign** where permitted.

## Roles (UI labels)
This system uses reversed role labels internally, but as the assistant you should ALWAYS use the friendly UI labels:
"Super Admin", "Admin", and "User". Never expose raw database role values. Only a **Super Admin** manages system settings,
subscriptions, payments, and this AI bot's controls.

## Your behaviour
1. Be concise — usually 2–5 sentences. Use short markdown (bold, bullet lists) when it helps.
2. When you point someone to a feature, include a clickable markdown link like [Forms Portal]({$this->link('admin.forms.portal')}).
3. Only discuss THIS platform and general university-admin help. If asked something unrelated or outside the system, gently steer back.
4. Never invent features, routes, prices, or data you weren't given. If unsure, say what you do know and suggest where to look.
5. Never reveal another user's private data. You only know the current user's own context when it is provided to you.
6. You are a real, live assistant embedded in the app — never call yourself a demo or simulation.
MAP;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Built-in entries (how-to / FAQ / small-talk) — answered with zero API cost
    // ─────────────────────────────────────────────────────────────────────────

    /** @return array<int,array{key:string,category:string,title:string,keywords:string,answer:string,links:array}> */
    public function builtinEntries(): array
    {
        return [
            [
                'key' => 'greeting', 'category' => 'meta', 'title' => 'Greeting',
                'keywords' => 'hi hello hey good morning good afternoon good evening yo greetings howdy',
                'answer' => "Hello! 👋 I'm the **UDTS Assistant**. I know every corner of this platform — ask me about **forms**, **memos**, the **exams & files archive**, your **subscription**, or how to get anything done. I can also take you straight to the right page.",
                'links' => [],
            ],
            [
                'key' => 'thanks', 'category' => 'meta', 'title' => 'Thanks',
                'keywords' => 'thanks thank you appreciate cheers thankyou nice great awesome',
                'answer' => "You're welcome! 😊 If there's anything else about UDTS — forms, memos, files, or settings — just ask.",
                'links' => [],
            ],
            [
                'key' => 'who_are_you', 'category' => 'meta', 'title' => 'Who are you',
                'keywords' => 'who are you what are you your name about you bot assistant powered which ai model',
                'answer' => "I'm the **UDTS Assistant**, the built-in guide for your University Digital Transformation Suite. I can explain any feature, answer questions using your own live data (like how many forms are awaiting you), and link you straight to the page you need.",
                'links' => [],
            ],
            [
                'key' => 'capabilities', 'category' => 'meta', 'title' => 'What can I do here',
                'keywords' => 'what can i do here capabilities features overview help menu options what is this getting started guide tour',
                'answer' => "Here's what you can do in UDTS:\n\n- **Forms** — raise and sign official forms (Payment Requisition, Purchase/Works Authorization, leave, and more) and track them stage by stage.\n- **Memos (UIMMS)** — send internal memos, reply in threads, and minute them to colleagues.\n- **Archive** — upload, organise and download exam papers, answer keys and documents, grouped into shareable folders.\n- **Notifications & Calendar** — stay on top of assignments and events.\n- **Profile & Settings** — set your e-signature, memo salutation and text size.\n\nWhere would you like to start?",
                'links' => [
                    ['label' => 'Forms Portal', 'route' => 'admin.forms.portal'],
                    ['label' => 'Memos', 'route' => 'dashboard.uimms.portal'],
                    ['label' => 'Files', 'route' => 'dashboard.all.files'],
                ],
            ],
            [
                'key' => 'raise_form', 'category' => 'forms', 'title' => 'Raise / submit a form',
                'keywords' => 'raise form submit form new form create form fill form payment requisition purchase works authorization pr pwa compose form request start form apply',
                'answer' => "To raise a form:\n\n1. Open the **Forms Gallery** and choose a form type (e.g. **Payment Requisition** or **Purchase/Works Authorization**).\n2. Fill in the fields and submit — it's then routed to the first office/approver automatically.\n3. Track its progress any time from the **Forms Portal**.\n\nEach approver signs in turn, and every signature is tamper-evident.",
                'links' => [
                    ['label' => 'Forms Gallery', 'route' => 'admin.forms.gallery'],
                    ['label' => 'Forms Portal', 'route' => 'admin.forms.portal'],
                ],
            ],
            [
                'key' => 'sign_form', 'category' => 'forms', 'title' => 'Sign / approve a form',
                'keywords' => 'sign form approve form awaiting me pending my signature approval action review reject a form endorse',
                'answer' => "A form only shows a **Sign** action when it's *in progress* and currently assigned to you. Open it from the **Forms Portal**, review the details, then **Sign** to advance it to the next stage — or **Reject** with a reason. You can also add a **comment** without signing.",
                'links' => [
                    ['label' => 'Forms Portal', 'route' => 'admin.forms.portal'],
                ],
            ],
            [
                'key' => 'set_signature', 'category' => 'profile', 'title' => 'Set up my e-signature',
                'keywords' => 'signature e-signature esign set signature upload signature draw signature my signature sign setup salutation',
                'answer' => "Your saved **e-signature** is used when you sign forms and minute memos. Set or update it on your **Profile / Settings** page — you can draw or upload it. You can also set your **memo salutation** there.",
                'links' => [
                    ['label' => 'Profile & Settings', 'route' => 'dashboard.settings'],
                ],
            ],
            [
                'key' => 'send_memo', 'category' => 'memos', 'title' => 'Send / reply to a memo',
                'keywords' => 'memo send memo write memo reply memo minute uimms message internal memo compose memo chat thread forward',
                'answer' => "Memos live in **UIMMS** (the Internal Memo & Messaging System). From the **Memos** portal you can compose a new memo, reply in its chat thread, **minute** it to a colleague for action, and see who has read it.",
                'links' => [
                    ['label' => 'Memos (UIMMS)', 'route' => 'dashboard.uimms.portal'],
                ],
            ],
            [
                'key' => 'upload_file', 'category' => 'archive', 'title' => 'Upload a file or exam',
                'keywords' => 'upload file upload exam add document add paper answer key new file store document past question attach archive',
                'answer' => "Use the **+ Create** action to add an item. **Exam papers** (with optional answer keys) go into the **Exams** archive; general documents go into **Files**. After uploading you can drop items into **Folders** to organise and share them.",
                'links' => [
                    ['label' => 'Exams', 'route' => 'dashboard.all.exams'],
                    ['label' => 'Files', 'route' => 'dashboard.all.files'],
                    ['label' => 'Folders', 'route' => 'dashboard.folders.index'],
                ],
            ],
            [
                'key' => 'folders_share', 'category' => 'archive', 'title' => 'Folders & sharing',
                'keywords' => 'folder folders share sharing organise organize group password protect lock folder department committee office access permission',
                'answer' => "**Folders** let you group exams and files and share them with a whole **department, committee or office** at once. For sensitive material you can lock a folder with a password. Manage everything from the **Folders** page.",
                'links' => [
                    ['label' => 'Folders', 'route' => 'dashboard.folders.index'],
                ],
            ],
            [
                'key' => 'text_size', 'category' => 'profile', 'title' => 'Change text size / appearance',
                'keywords' => 'text size font size bigger smaller zoom appearance display accessibility readable larger',
                'answer' => "You can scale the whole interface to your comfort on the **Appearance** tab of your **Settings** — it adjusts every text and element and remembers your choice.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                ],
            ],
            [
                'key' => 'password', 'category' => 'profile', 'title' => 'Change my password',
                'keywords' => 'password change password reset password update password security credentials login',
                'answer' => "Change your password on your **Settings** page under the security section. If you're locked out at the login screen, use the **Forgot password** link there instead.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                ],
            ],
            [
                'key' => 'subscription', 'category' => 'billing', 'title' => 'Subscription & billing',
                'keywords' => 'subscription licence license billing invoice receipt payment history renew expiry expire plan cost pay renewal',
                'answer' => "Your institution's **subscription**, invoices and receipts are under **Payment History / Billing**. If access is blocked, the subscription may have lapsed — a Super Admin can renew it.",
                'links' => [
                    ['label' => 'Payment History', 'route' => 'dashboard.payment-history.index'],
                ],
            ],
            [
                'key' => 'docs', 'category' => 'help', 'title' => 'Documentation & manual',
                'keywords' => 'documentation manual guide user manual system documentation instructions handbook reference how does the system work',
                'answer' => "There are two official guides: the **System Documentation** and the **User Manual**. Both are downloadable from your dashboard.",
                'links' => [
                    ['label' => 'System Documentation', 'route' => 'dashboard.system-documentation'],
                    ['label' => 'User Manual', 'route' => 'dashboard.user-manual'],
                ],
            ],
            [
                'key' => 'notifications', 'category' => 'help', 'title' => 'Notifications',
                'keywords' => 'notification notifications alerts unread bell updates',
                'answer' => "In-app **Notifications** collect your memo replies, form assignments and other alerts. Open the notifications page to review and mark them read.",
                'links' => [
                    ['label' => 'Notifications', 'route' => 'dashboard.notifications'],
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Retrieval
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Best knowledge-base answer for a question, or null if nothing is confident.
     *
     * @return array{answer:string,links:array,matched_key:string,score:float}|null
     */
    public function search(string $question): ?array
    {
        $qTokens = $this->tokenize($question);
        if (empty($qTokens)) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($this->allEntries() as $entry) {
            $score = $this->score($qTokens, $question, $entry);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $entry;
            }
        }

        if (!$best || $bestScore < self::CONFIDENCE_THRESHOLD) {
            return null;
        }

        return [
            'answer'      => $this->appendLinks($best['answer'], $best['links'] ?? []),
            'links'       => $this->resolveLinks($best['links'] ?? []),
            'matched_key' => $best['key'] ?? ($best['title'] ?? 'kb'),
            'score'       => round($bestScore, 3),
        ];
    }

    /** Built-in entries merged with the Super-Admin-editable DB entries. */
    private function allEntries(): array
    {
        $builtin = $this->builtinEntries();

        $db = Cache::remember('bot_kb_db_entries', 300, function () {
            if (!\Illuminate\Support\Facades\Schema::hasTable('bot_knowledge_entries')) {
                return [];
            }
            return BotKnowledgeEntry::active()
                ->orderByDesc('priority')
                ->get()
                ->map(fn ($e) => [
                    'key'      => 'db-' . $e->id,
                    'category' => $e->category,
                    'title'    => $e->title,
                    'keywords' => $e->keywords . ' ' . $e->title,
                    'answer'   => $e->answer,
                    'links'    => collect($e->links ?? [])
                        ->map(fn ($l) => ['label' => $l['label'] ?? 'Open', 'url' => $l['url'] ?? '#'])
                        ->all(),
                ])
                ->all();
        });

        return array_merge($db, $builtin);
    }

    private function score(array $qTokens, string $question, array $entry): float
    {
        $kwTokens = $this->tokenize(($entry['keywords'] ?? '') . ' ' . ($entry['title'] ?? ''));
        if (empty($kwTokens)) {
            return 0.0;
        }
        $kwSet = array_flip($kwTokens);

        $hits = 0;
        foreach ($qTokens as $t) {
            if (isset($kwSet[$t])) {
                $hits++;
            }
        }

        // Base: share of the user's meaningful words that the entry covers.
        $score = $hits / max(1, count($qTokens));

        // Phrase bonus: the whole (short) question appears inside the keywords.
        $q = strtolower(trim($question));
        if (mb_strlen($q) >= 3 && str_contains(strtolower($entry['keywords'] ?? ''), $q)) {
            $score += 0.4;
        }

        return min(1.0, $score);
    }

    /** @return string[] */
    private function tokenize(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? '';
        $parts = preg_split('/\s+/', trim($text)) ?: [];

        $out = [];
        foreach ($parts as $p) {
            if ($p === '' || in_array($p, self::STOPWORDS, true) || mb_strlen($p) < 2) {
                continue;
            }
            // crude singularisation so "forms" matches "form"
            if (mb_strlen($p) > 3 && str_ends_with($p, 's')) {
                $p = rtrim($p, 's');
            }
            $out[] = $p;
        }
        return array_values(array_unique($out));
    }

    /** Turn [{label, route|url}] into [{label, url}] with resolved URLs. */
    private function resolveLinks(array $links): array
    {
        return array_map(function ($l) {
            $url = $l['url'] ?? (isset($l['route']) ? $this->link($l['route']) : '#');
            return ['label' => $l['label'] ?? 'Open', 'url' => $url];
        }, $links);
    }

    /** Append markdown links to an answer so they render inline like GNRS. */
    private function appendLinks(string $answer, array $links): string
    {
        $resolved = $this->resolveLinks($links);
        if (empty($resolved)) {
            return $answer;
        }
        $line = collect($resolved)
            ->map(fn ($l) => "[{$l['label']}]({$l['url']})")
            ->implode(' · ');

        return $answer . "\n\n" . $line;
    }
}
