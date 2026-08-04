<?php

namespace App\Services\Bot;

use App\Models\BotKnowledgeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * The bot's local "brain".
 *
 * Two jobs:
 *  1. {@see systemMap()} — a complete, ACCURATE description of the UDTS platform,
 *     fed to Gemini as the grounding prompt so it never invents features or links.
 *  2. {@see search()} — a zero-cost retrieval layer. Built-in how-to/FAQ entries
 *     plus Super-Admin-editable {@see BotKnowledgeEntry} rows are scored against the
 *     user's question by token overlap; a confident match is answered instantly.
 *
 * ACCURACY IS THE CONTRACT: every link here mirrors the real sidebar navigation
 * (resources/views/components/sidebar.blade.php). Some destinations differ by
 * account type (reversed-role split), so those are resolved per-user via
 * {@see memoComposeRoute()} etc.
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

    // The memo compose / list pages differ by account type (reversed-role split).
    private function memoComposeRoute(?User $user): string
    {
        return ($user && $user->is_admin) ? 'admin.communication-admin.create' : 'admin.communication.create';
    }

    private function memoIndexRoute(?User $user): string
    {
        return ($user && $user->is_admin) ? 'admin.communication-admin.index' : 'admin.communication.index';
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  System map — fed to Gemini as the grounding system prompt
    // ─────────────────────────────────────────────────────────────────────────

    public function systemMap(): string
    {
        return <<<MAP
You are **MetaGuide** — the official in-app AI guide for the University Digital Transformation Suite (UDTS)
by Metascholar Consult. You are warm, concise, and accurate. You know every corner of this platform, and you can
point a user to the exact page they need with a clickable link.

## What UDTS is
An internal institutional platform for a university/college: a secure examinations & document archive, an
organisational Forms workflow with e-signing, an internal memo system (UIMMS), and institutional administration
(offices, committees, departments, subscriptions).

## Navigation — use the exact sidebar labels
### Internal Memo Management System (UIMMS)
- **Compose Memo** — this is where you WRITE and SEND a memo (pick recipients, attach files, send). Composing is NOT done from the portal.
- **Memos Portal** ({$this->link('dashboard.uimms.portal')}) — READ memos, reply in a chat thread, **minute** a memo to a colleague for action, and see who has read it.
- **Keep in View** ({$this->link('dashboard.uimms.keep-in-view')}) — memos you bookmarked to follow up.
- **Memos** — the list of memos you have sent.
> When asked how to compose/send a memo, tell the user to click **Compose Memo** in the sidebar's Internal Memo Management System section. (The exact page differs slightly by account type, so name the button rather than a raw URL.)

### Forms Workflow
- **All Forms** ({$this->link('admin.forms.gallery')}) — the gallery of form types. To RAISE a form, open this, choose a form (e.g. Payment Requisition or Purchase/Works Authorization), fill it in and submit.
- **Forms Portal** ({$this->link('admin.forms.portal')}) — track your forms and act on ones assigned to you (sign, reject, comment). A form moves stage-by-stage between offices/leaders; each signs in turn and every signature is tamper-evident.

### Exams & Files archive
- **Exams** ({$this->link('dashboard.all.exams')}) — browse/download exam papers & answer keys.
- **Files** ({$this->link('dashboard.all.files')}) — browse/download general documents.
- **Uploading:** use the **+ Create** button in the top header to add an exam or a file. (There is no separate upload link in the sidebar.)
- **My Folders** ({$this->link('dashboard.folders.index')}) and **Departmental Folders** ({$this->link('dashboard.departmental-folders')}) — organise items into folders and share them with a department, committee or office. Sensitive folders can be locked with a password.

### Organisation & account
- **Committees** — manage committees; **My Committees** ({$this->link('committees.my-committees')}) shows the ones you belong to.
- **Offices** ({$this->link('offices.index')}) — institutional offices (Finance, Internal Audit, Registrar, VC, Procurement Committee, Director of Finance) that forms route through.
- **Billing / Payment History** ({$this->link('dashboard.payment-history.index')}) — the institution's subscription invoices and receipts.
- **Settings** ({$this->link('dashboard.settings')}) — change password, set your **e-signature**, set your **memo salutation**, and adjust **text size** on the Appearance tab.
- **Profile** ({$this->link('dashboard.profile')}) — your personal details.
- **Notifications** ({$this->link('dashboard.notifications')}) — in-app alerts. **Search** ({$this->link('search.index')}) — global search (also ⌘K / Ctrl-K).
- **System Documentation** ({$this->link('dashboard.system-documentation')}) and **User Manual** ({$this->link('dashboard.user-manual')}) — official guides.

## Roles
Always use the friendly UI labels only — "Super Admin", "Admin", "User". Never expose raw database role values.
Only a **Super Admin** manages system settings, subscriptions, payments, and this bot.

## Accounts — who can change what (IMPORTANT)
- A user can change their OWN: **name, profile picture, password, e-signature, memo salutation, and text size** (in Settings / Profile).
- An **administrator** manages a user's **position, department, staff category, email, role, account approval, and account creation/deletion**. These drive official routing (e.g. who signs which forms), so users cannot change them themselves — this is a deliberate security rule.
- So when someone asks to change their **position, department, role or email** (or "my credentials"), tell them those are set by an administrator and they should **contact their administrator**, and remind them what they *can* change themselves.

## Your behaviour — accuracy first, and genuinely helpful
1. You cover the ENTIRE platform — every feature and corner above. Never imply you only handle a few topics. Treat every genuine question seriously and give a real, specific answer.
2. Be concise (usually 2–5 sentences). Use short markdown (bold, bullets) when it helps.
3. Point people to features with a clickable markdown link, e.g. [Forms Portal]({$this->link('admin.forms.portal')}). Use ONLY the pages listed above — never invent a page, route, price or feature.
4. If you are not certain a link is correct, name the sidebar button instead of guessing a URL. If something is outside what a user can do themselves, say who handles it (usually their administrator).
5. Never reveal another user's private data; you only know the current user's own context when it is provided.
6. Discuss only this platform and general university-admin help. You are a real, live assistant — never call yourself a demo.
MAP;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Built-in entries (how-to / FAQ / small-talk) — answered with zero API cost
    // ─────────────────────────────────────────────────────────────────────────

    /** @return array<int,array{key:string,category:string,title:string,keywords:string,answer:string,links:array}> */
    public function builtinEntries(?User $user = null): array
    {
        $memoCompose = $this->memoComposeRoute($user);
        $memoIndex   = $this->memoIndexRoute($user);

        return [
            // ── meta / small-talk ──────────────────────────────────────────
            [
                'key' => 'greeting', 'category' => 'meta', 'title' => 'Greeting',
                'keywords' => 'hi hello hey good morning good afternoon good evening yo greetings howdy',
                'answer' => "Hello! 👋 I'm **MetaGuide**. I can help with **memos**, **forms**, the **exams & files archive**, your **subscription**, or settings — and take you straight to the right page. What do you need?",
                'links' => [],
            ],
            [
                'key' => 'thanks', 'category' => 'meta', 'title' => 'Thanks',
                'keywords' => 'thanks thank you appreciate cheers thankyou nice great awesome',
                'answer' => "You're welcome! 😊 Anything else I can help with?",
                'links' => [],
            ],
            [
                'key' => 'who_are_you', 'category' => 'meta', 'title' => 'Who are you',
                'keywords' => 'who are you what are you your name about you assistant powered which ai model metaguide',
                'answer' => "I'm **MetaGuide**, the built-in assistant for UDTS. I can explain any feature, answer questions from your own live data (like how many forms are awaiting you), and take you straight to the page you need.",
                'links' => [],
            ],
            [
                'key' => 'capabilities', 'category' => 'meta', 'title' => 'What can I do here',
                'keywords' => 'what can i do here capabilities features overview help menu options what is this getting started guide tour',
                'answer' => "Here's what UDTS covers:\n\n- **Memos (UIMMS)** — compose, send, reply in threads, minute to colleagues, track reads.\n- **Forms** — raise official forms (Payment Requisition, Purchase/Works Authorization, leave, etc.) and sign them stage by stage.\n- **Archive** — upload, organise and download exam papers, answer keys and documents, grouped into shareable folders.\n- **Notifications, Calendar & Search** — stay on top of everything.\n- **Settings** — your e-signature, memo salutation and text size.\n\nWhere would you like to start?",
                'links' => [
                    ['label' => 'Compose Memo', 'route' => $memoCompose],
                    ['label' => 'All Forms', 'route' => 'admin.forms.gallery'],
                    ['label' => 'Files', 'route' => 'dashboard.all.files'],
                ],
            ],

            // ── memos ──────────────────────────────────────────────────────
            [
                'key' => 'compose_memo', 'category' => 'memos', 'title' => 'Compose / send a memo',
                'keywords' => 'compose memo write memo send memo new memo create memo draft memo start memo message someone circulate internal memo',
                'answer' => "To compose a memo, open **Compose Memo** in the *Internal Memo Management System* section of the sidebar. There you write the memo, pick recipients, attach files if needed, and send it. To read, reply or track memos afterwards, use the **Memos Portal**.",
                'links' => [
                    ['label' => 'Compose Memo', 'route' => $memoCompose],
                    ['label' => 'Memos Portal', 'route' => 'dashboard.uimms.portal'],
                ],
            ],
            [
                'key' => 'memo_portal', 'category' => 'memos', 'title' => 'Read / reply to memos',
                'keywords' => 'read memo reply memo respond memo chat thread memo portal inbox received memos view memos who read read receipt',
                'answer' => "Open the **Memos Portal** to read your memos, reply in the chat thread, and see who has read each one. Your **sent** memos are under the **Memos** list.",
                'links' => [
                    ['label' => 'Memos Portal', 'route' => 'dashboard.uimms.portal'],
                    ['label' => 'My Sent Memos', 'route' => $memoIndex],
                ],
            ],
            [
                'key' => 'minute_memo', 'category' => 'memos', 'title' => 'Minute / forward a memo',
                'keywords' => 'minute memo forward memo assign memo refer memo delegate memo action memo pass to colleague',
                'answer' => "Inside a memo in the **Memos Portal**, you can **minute** it to a colleague for action — an official, signed instruction — or forward it through an intermediary. Everyone sees the trail.",
                'links' => [
                    ['label' => 'Memos Portal', 'route' => 'dashboard.uimms.portal'],
                ],
            ],
            [
                'key' => 'keep_in_view', 'category' => 'memos', 'title' => 'Bookmark a memo (Keep in View)',
                'keywords' => 'keep in view bookmark memo follow up flag memo save memo watch memo pin',
                'answer' => "Use **Keep in View** to bookmark memos you want to follow up on — they're collected on their own page so nothing slips through.",
                'links' => [
                    ['label' => 'Keep in View', 'route' => 'dashboard.uimms.keep-in-view'],
                ],
            ],

            // ── forms ──────────────────────────────────────────────────────
            [
                'key' => 'raise_form', 'category' => 'forms', 'title' => 'Raise / submit a form',
                'keywords' => 'raise form submit form new form create form fill form request form payment requisition purchase works authorization pr pwa leave application start form apply',
                'answer' => "To raise a form, open **All Forms**, choose the form type you need (e.g. **Payment Requisition** or **Purchase/Works Authorization**), fill it in and submit. It's then routed to the first approver automatically. Track its progress any time in the **Forms Portal**.",
                'links' => [
                    ['label' => 'All Forms', 'route' => 'admin.forms.gallery'],
                    ['label' => 'Forms Portal', 'route' => 'admin.forms.portal'],
                ],
            ],
            [
                'key' => 'sign_form', 'category' => 'forms', 'title' => 'Sign / approve / reject a form',
                'keywords' => 'sign form approve form reject form endorse form awaiting me pending my signature approval action review a form comment on form',
                'answer' => "A form shows a **Sign** action only when it's *in progress* and currently assigned to you. Open it from the **Forms Portal**, review it, then **Sign** to advance it — or **Reject** with a reason. You can also add a **comment** without signing.",
                'links' => [
                    ['label' => 'Forms Portal', 'route' => 'admin.forms.portal'],
                ],
            ],
            [
                'key' => 'track_form', 'category' => 'forms', 'title' => 'Track a form / where is it',
                'keywords' => 'track form form status where is my form form progress stage of form who has it pending approval outstanding form pdf download form',
                'answer' => "The **Forms Portal** shows every form you raised or are involved in, and which stage it's at. Open a form to see its full history and download its **PDF**.",
                'links' => [
                    ['label' => 'Forms Portal', 'route' => 'admin.forms.portal'],
                ],
            ],

            // ── signature / salutation ─────────────────────────────────────
            [
                'key' => 'set_signature', 'category' => 'profile', 'title' => 'Set up my e-signature',
                'keywords' => 'signature e-signature esign set signature upload signature draw signature my signature how do i sign',
                'answer' => "Your saved **e-signature** is used when you sign forms and minute memos. Set or update it on your **Settings** page — you can draw or upload it.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                ],
            ],
            [
                'key' => 'memo_salutation', 'category' => 'profile', 'title' => 'Set my memo salutation',
                'keywords' => 'memo salutation signature block sign off title on memo my salutation how i appear on memos',
                'answer' => "Your **memo salutation** (how your name/title appears on memos you minute) is set on your **Settings** page.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                ],
            ],

            // ── archive ────────────────────────────────────────────────────
            [
                'key' => 'upload_exam', 'category' => 'archive', 'title' => 'Upload an exam / past question',
                'keywords' => 'upload exam add exam new exam past question answer key exam paper store exam add paper',
                'answer' => "Use the **+ Create** button in the top header to add an **exam** (with an optional answer key). Your uploaded exams then appear under **Exams**, and you can drop them into folders to organise and share.",
                'links' => [
                    ['label' => '+ Create an exam', 'route' => 'dashboard.create'],
                    ['label' => 'Exams', 'route' => 'dashboard.all.exams'],
                ],
            ],
            [
                'key' => 'upload_file', 'category' => 'archive', 'title' => 'Upload a file / document',
                'keywords' => 'upload file add document new file store document attach document add file upload paperwork',
                'answer' => "Use the **+ Create** button in the top header to add a **file/document**. It then appears under **Files**, ready to organise into folders.",
                'links' => [
                    ['label' => '+ Create a file', 'route' => 'dashboard.file.create'],
                    ['label' => 'Files', 'route' => 'dashboard.all.files'],
                ],
            ],
            [
                'key' => 'browse_archive', 'category' => 'archive', 'title' => 'Find / download exams & files',
                'keywords' => 'find exam download exam browse files download file where are my files my exams open document search archive',
                'answer' => "Browse and download from **Exams** and **Files**. For anything specific, the global **Search** (or ⌘K / Ctrl-K) looks across the whole archive.",
                'links' => [
                    ['label' => 'Exams', 'route' => 'dashboard.all.exams'],
                    ['label' => 'Files', 'route' => 'dashboard.all.files'],
                    ['label' => 'Search', 'route' => 'search.index'],
                ],
            ],
            [
                'key' => 'folders', 'category' => 'archive', 'title' => 'Folders & sharing',
                'keywords' => 'folder folders share sharing organise organize group my folders departmental folders department committee office access give access',
                'answer' => "**Folders** group exams and files so you can share them with a whole **department, committee or office** at once. Use **My Folders** for your own, or **Departmental Folders** for shared team spaces.",
                'links' => [
                    ['label' => 'My Folders', 'route' => 'dashboard.folders.index'],
                    ['label' => 'Departmental Folders', 'route' => 'dashboard.departmental-folders'],
                ],
            ],
            [
                'key' => 'folder_password', 'category' => 'archive', 'title' => 'Lock a folder with a password',
                'keywords' => 'folder password lock folder protect folder secure folder sensitive folder restrict folder',
                'answer' => "For sensitive material you can lock a folder with a password from its security settings in **My Folders** — only people with the password (and access) can open it.",
                'links' => [
                    ['label' => 'My Folders', 'route' => 'dashboard.folders.index'],
                ],
            ],

            // ── organisation ───────────────────────────────────────────────
            [
                'key' => 'committees', 'category' => 'org', 'title' => 'Committees',
                'keywords' => 'committee committees my committees join committee members of committee',
                'answer' => "**My Committees** shows the committees you belong to and their shared spaces. Committee membership is also used when sharing folders.",
                'links' => [
                    ['label' => 'My Committees', 'route' => 'committees.my-committees'],
                ],
            ],
            [
                'key' => 'offices', 'category' => 'org', 'title' => 'Offices',
                'keywords' => 'office offices finance registrar internal audit procurement vc director of finance who is in office routing office',
                'answer' => "**Offices** are the institutional offices (Finance, Internal Audit, Registrar, VC, Procurement Committee, Director of Finance) that forms route through. You can view offices and their active members on the Offices page.",
                'links' => [
                    ['label' => 'Offices', 'route' => 'offices.index'],
                ],
            ],

            // ── account / help ─────────────────────────────────────────────
            [
                'key' => 'subscription', 'category' => 'billing', 'title' => 'Subscription & billing',
                'keywords' => 'subscription licence license billing invoice receipt payment history renew expiry expire plan cost pay when does it expire',
                'answer' => "Your institution's **subscription**, invoices and receipts are under **Billing / Payment History**. If access is ever blocked, the subscription may have lapsed — a Super Admin can renew it.",
                'links' => [
                    ['label' => 'Payment History', 'route' => 'dashboard.payment-history.index'],
                ],
            ],
            [
                'key' => 'text_size', 'category' => 'profile', 'title' => 'Change text size / appearance',
                'keywords' => 'text size font size bigger smaller zoom appearance display accessibility readable larger interface size',
                'answer' => "Scale the whole interface to your comfort on the **Appearance** tab of **Settings** — it adjusts every text and element and remembers your choice.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                ],
            ],
            [
                'key' => 'password', 'category' => 'profile', 'title' => 'Change my password',
                'keywords' => 'password change password reset password update password login passcode',
                'answer' => "Change your password on your **Settings** page. If you're locked out at the login screen, use the **Forgot password** link there instead.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                ],
            ],
            [
                'key' => 'account_admin_managed', 'category' => 'account',
                'title' => 'Change my position, department, role or email',
                'keywords' => 'change my position change role change department change email update position job title designation promote credentials official details staff category who can change my position update my role update my details change my organization change job title fix my position wrong position',
                'answer' => "Your **position, department, staff category, email and role** are set by an **administrator**, not by you. These control official routing (for example, who signs which forms), so for security only a System Administrator can change them.\n\n**Please contact your administrator** to update your position, department or email.\n\nWhat you *can* change yourself in **Settings** / **Profile**: your **name**, **profile picture**, **password**, **e-signature**, **memo salutation** and **text size**.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                    ['label' => 'Profile', 'route' => 'dashboard.profile'],
                ],
            ],
            [
                'key' => 'account_self_service', 'category' => 'account',
                'title' => 'Update my own details (name, picture, password)',
                'keywords' => 'edit my account update my profile my details what can i change my information change my name update photo profile picture personal details my account settings',
                'answer' => "You can update these yourself in **Settings** / **Profile**: your **name**, **profile picture**, **password**, **e-signature**, **memo salutation** and **text size**. Your **position, department, email and role** are managed by an **administrator** — contact them for those.",
                'links' => [
                    ['label' => 'Settings', 'route' => 'dashboard.settings'],
                    ['label' => 'Profile', 'route' => 'dashboard.profile'],
                ],
            ],
            [
                'key' => 'notifications', 'category' => 'help', 'title' => 'Notifications',
                'keywords' => 'notification notifications alerts unread bell updates',
                'answer' => "**Notifications** collect your memo replies, form assignments and other alerts. Open the notifications page to review and mark them read.",
                'links' => [
                    ['label' => 'Notifications', 'route' => 'dashboard.notifications'],
                ],
            ],
            [
                'key' => 'search', 'category' => 'help', 'title' => 'Search the system',
                'keywords' => 'search find lookup command palette ctrl k cmd k global search look for',
                'answer' => "Use the global **Search** — or press **⌘K / Ctrl-K** anywhere — to find files, folders, exams, memos, forms, people and pages.",
                'links' => [
                    ['label' => 'Search', 'route' => 'search.index'],
                ],
            ],
            [
                'key' => 'docs', 'category' => 'help', 'title' => 'Documentation & manual',
                'keywords' => 'documentation manual guide user manual system documentation instructions handbook reference how does the system work',
                'answer' => "Two official guides are available: the **System Documentation** and the **User Manual**, both downloadable from your dashboard.",
                'links' => [
                    ['label' => 'System Documentation', 'route' => 'dashboard.system-documentation'],
                    ['label' => 'User Manual', 'route' => 'dashboard.user-manual'],
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
    public function search(string $question, ?User $user = null): ?array
    {
        $qTokens = $this->tokenize($question);
        if (empty($qTokens)) {
            return null;
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($this->allEntries($user) as $entry) {
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
    private function allEntries(?User $user = null): array
    {
        $builtin = $this->builtinEntries($user);

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
