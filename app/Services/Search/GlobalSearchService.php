<?php

namespace App\Services\Search;

use App\Models\Committee;
use App\Models\Department;
use App\Models\EmailCampaign;
use App\Models\Exam;
use App\Models\File;
use App\Models\Folder;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\Position;
use App\Models\SystemDocumentation;
use App\Models\User;
use App\Models\UserManual;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Single source of truth for the global header search.
 *
 * SECURITY: every query below re-uses an access rule that already exists
 * elsewhere in the app, so search can NEVER widen a user's visibility:
 *   - Files / Exams  → owner OR inside an accessible folder  (FilesController::downloadFile)
 *   - Folders        → owned OR Folder::sharedWith (direct shares + group grants)
 *   - Memos          → creator OR recipient                  (HomeController::memoChat)
 *   - Forms          → FormSubmissionPolicy::view
 *   - Committees      → membership                            (CommitteesController::show)
 *   - People/Dept/Position/Office/management pages → gated on the same `is_admin`
 *     column the sidebar's management block uses.
 *
 * Result links point only to permission-checked destinations, each of which
 * still aborts 403 on unauthorised direct access.
 */
class GlobalSearchService
{
    /**
     * Group key => display label. Iteration order here is the display order.
     */
    public const GROUPS = [
        'pages'       => 'Pages',
        'files'       => 'Files',
        'folders'     => 'Folders',
        'exams'       => 'Exams',
        'forms'       => 'Forms',
        'memos'       => 'Memos',
        'committees'  => 'Committees',
        'people'      => 'People',
        'departments' => 'Departments',
        'positions'   => 'Positions',
        'offices'     => 'Offices',
        'policies'    => 'University Policies',
        'manuals'     => 'User Manual',
    ];

    /**
     * Run the search and return only the non-empty groups, in display order.
     *
     * @param  array<int,string>  $types  optional whitelist of group keys
     * @return array<string,array{key:string,label:string,items:array,has_more:bool}>
     */
    public function search(User $user, string $q, array $types = [], int $perGroup = 5): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }

        $like = '%' . addcslashes($q, '%_\\') . '%';

        // Institutional-admin tier — the exact condition InstitutionalAdminMiddleware
        // enforces (super admin OR reversed-role "Admin", i.e. is_admin = false).
        // This gates People / Offices (both institutional_admin routes) as well as
        // the Departments / Positions / management page results.
        $isManager = $user->isSuperAdmin() || ! $user->is_admin;

        // Access sets — computed once, reused across groups.
        $ownedFolderIds      = Folder::where('user_id', $user->id)->pluck('id');
        $sharedFolderIds     = Folder::sharedWith($user)->pluck('folders.id');
        $accessibleFolderIds = $ownedFolderIds->merge($sharedFolderIds)->unique()->values();
        $activeOfficeIds     = $user->activeOffices()->pluck('offices.id');

        $wanted = fn (string $key): bool => empty($types) || in_array($key, $types, true);

        $groups = [];

        foreach (array_keys(self::GROUPS) as $key) {
            if (! $wanted($key)) {
                continue;
            }

            $rows = match ($key) {
                'pages'       => $this->pages($q, $isManager, $perGroup),
                'files'       => $this->files($user, $like, $accessibleFolderIds, $perGroup),
                'folders'     => $this->folders($like, $accessibleFolderIds, $perGroup),
                'exams'       => $this->exams($user, $like, $accessibleFolderIds, $perGroup),
                'forms'       => $this->forms($user, $like, $activeOfficeIds, $perGroup),
                'memos'       => $this->memos($user, $like, $perGroup),
                'committees'  => $this->committees($user, $like, $perGroup),
                'people'      => $isManager ? $this->people($like, $perGroup) : $this->none(),
                'departments' => $isManager ? $this->departments($like, $perGroup) : $this->none(),
                'positions'   => $isManager ? $this->positions($like, $perGroup) : $this->none(),
                'offices'     => $isManager ? $this->offices($like, $perGroup) : $this->none(),
                'policies'    => $this->policies($like, $perGroup),
                'manuals'     => $this->manuals($like, $perGroup),
                default       => $this->none(),
            };

            if (! empty($rows['items'])) {
                $groups[$key] = [
                    'key'      => $key,
                    'label'    => self::GROUPS[$key],
                    'items'    => $rows['items'],
                    'has_more' => $rows['has_more'],
                ];
            }
        }

        return $groups;
    }

    // ── Group builders ──────────────────────────────────────────────────────

    private function files(User $user, string $like, $accessibleFolderIds, int $perGroup): array
    {
        $rows = File::query()
            ->where(function ($w) use ($user, $accessibleFolderIds) {
                $w->where('user_id', $user->id);
                if ($accessibleFolderIds->isNotEmpty()) {
                    $w->orWhereHas('folders', fn ($f) => $f->whereIn('folders.id', $accessibleFolderIds));
                }
            })
            ->where('file_title', 'like', $like)
            ->latest()
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (File $f) => $this->make(
            'file',
            $f->file_title ?: 'Untitled file',
            trim(($f->unit ? $f->unit . ' • ' : '') . ($f->file_format ?: strtoupper(pathinfo($f->document_file ?? '', PATHINFO_EXTENSION)))),
            route('download.file', $f->id),
            $f->file_format,
            optional($f->created_at)->format('M j, Y'),
        ));
    }

    private function folders(string $like, $accessibleFolderIds, int $perGroup): array
    {
        if ($accessibleFolderIds->isEmpty()) {
            return $this->none();
        }

        $rows = Folder::whereIn('id', $accessibleFolderIds)
            ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('description', 'like', $like))
            ->withCount(['files', 'exams'])
            ->latest('updated_at')
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (Folder $f) => $this->make(
            'folder',
            $f->name ?: 'Untitled folder',
            $f->files_count . ' ' . Str::plural('file', $f->files_count) . ' • ' . $f->exams_count . ' ' . Str::plural('exam', $f->exams_count),
            route('dashboard.folders.show', $f->id),
            null,
            optional($f->updated_at)->format('M j, Y'),
        ));
    }

    private function exams(User $user, string $like, $accessibleFolderIds, int $perGroup): array
    {
        $rows = Exam::query()
            ->where(function ($w) use ($user, $accessibleFolderIds) {
                $w->where('user_id', $user->id);
                if ($accessibleFolderIds->isNotEmpty()) {
                    $w->orWhereHas('folders', fn ($f) => $f->whereIn('folders.id', $accessibleFolderIds));
                }
            })
            ->where(fn ($w) => $w->where('course_title', 'like', $like)
                ->orWhere('course_code', 'like', $like)
                ->orWhere('instructor_name', 'like', $like))
            ->with('folders:id')
            ->latest()
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, function (Exam $e) use ($user, $accessibleFolderIds) {
            // Owner → the exams listing; otherwise the accessible folder holding it.
            $url = route('dashboard.all.exams');
            if ($e->user_id !== $user->id) {
                $folderId = $e->folders->pluck('id')->intersect($accessibleFolderIds)->first();
                if ($folderId) {
                    $url = route('dashboard.folders.show', $folderId);
                }
            }

            return $this->make(
                'exam',
                $e->course_title ?: $e->course_code ?: 'Exam',
                trim(($e->course_code ? $e->course_code . ' • ' : '') . ($e->instructor_name ?: '')),
                $url,
                $e->course_code,
                optional($e->exam_date)->format('M j, Y'),
            );
        });
    }

    private function memos(User $user, string $like, int $perGroup): array
    {
        $rows = EmailCampaign::query()
            ->where(function ($w) use ($user) {
                $w->where('created_by', $user->id)
                    ->orWhereHas('recipients', fn ($r) => $r->where('user_id', $user->id));
            })
            ->where(fn ($w) => $w->where('subject', 'like', $like)
                ->orWhere('reference', 'like', $like)
                ->orWhere('message', 'like', $like))
            ->with('creator:id,first_name,last_name')
            ->latest()
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, function (EmailCampaign $m) {
            $sender   = $m->creator ? trim($m->creator->first_name . ' ' . $m->creator->last_name) : null;
            $subtitle = trim(($sender ? 'From ' . $sender : '') . ($m->reference ? ' • ' . $m->reference : ''));

            return $this->make(
                'memo',
                $m->subject ?: '(No subject)',
                $subtitle ?: 'Memo',
                route('dashboard.uimms.chat', $m->id),
                $m->memo_status,
                optional($m->created_at)->format('M j, Y'),
            );
        });
    }

    private function forms(User $user, string $like, $activeOfficeIds, int $perGroup): array
    {
        $rows = FormSubmission::query()
            ->where(function ($w) use ($user, $activeOfficeIds) {
                $w->where('created_by', $user->id)
                    ->orWhere('current_assignee_id', $user->id)
                    ->orWhereHas('signatures', fn ($s) => $s->where('user_id', $user->id));
                if ($activeOfficeIds->isNotEmpty()) {
                    $w->orWhereIn('current_office_id', $activeOfficeIds);
                }
            })
            ->where(fn ($w) => $w->where('reference', 'like', $like)
                ->orWhere('title', 'like', $like)
                ->orWhere('form_code', 'like', $like))
            ->latest('updated_at')
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (FormSubmission $s) => $this->make(
            'form',
            $s->title ?: $s->form_code ?: 'Form',
            trim(($s->reference ? $s->reference : '') . ($s->form_code ? ' • ' . $s->form_code : '')),
            route('admin.forms.show', $s->id),
            $s->status,
            optional($s->updated_at)->format('M j, Y'),
        ));
    }

    private function committees(User $user, string $like, int $perGroup): array
    {
        $rows = $user->committees()
            ->where(fn ($w) => $w->where('committees.name', 'like', $like)
                ->orWhere('committees.description', 'like', $like))
            ->withCount('users')
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (Committee $c) => $this->make(
            'committee',
            $c->name,
            $c->users_count . ' ' . Str::plural('member', $c->users_count),
            route('committees.show', $c->id),
            $c->status,
            null,
        ));
    }

    private function people(string $like, int $perGroup): array
    {
        $rows = User::where('is_approve', true)
            ->where(function ($w) use ($like) {
                $w->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
            })
            ->with('position:id,name')
            ->orderBy('first_name')
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (User $u) => $this->make(
            'user',
            trim($u->first_name . ' ' . $u->last_name) ?: $u->email,
            trim($u->email . ($u->position ? ' • ' . $u->position->name : '')),
            route('dashboard.users'),
            null,
            null,
        ));
    }

    private function departments(string $like, int $perGroup): array
    {
        $rows = Department::where('name', 'like', $like)
            ->orderBy('name')
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (Department $d) => $this->make(
            'department',
            $d->name,
            'Department / Faculty',
            route('departments.index'),
            null,
            null,
        ));
    }

    private function positions(string $like, int $perGroup): array
    {
        $rows = Position::where('name', 'like', $like)
            ->orderBy('name')
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (Position $p) => $this->make(
            'position',
            $p->name,
            'Position',
            route('positions.index'),
            null,
            null,
        ));
    }

    private function offices(string $like, int $perGroup): array
    {
        $rows = Office::where(fn ($w) => $w->where('name', 'like', $like)
            ->orWhere('slug', 'like', $like)
            ->orWhere('description', 'like', $like))
            ->withCount('activeUsers')
            ->orderBy('name')
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (Office $o) => $this->make(
            'office',
            $o->name,
            $o->active_users_count . ' active ' . Str::plural('member', $o->active_users_count),
            route('offices.show', $o->id),
            $o->is_active ? null : 'inactive',
            null,
        ));
    }

    private function policies(string $like, int $perGroup): array
    {
        $rows = SystemDocumentation::where(fn ($w) => $w->where('title', 'like', $like)
            ->orWhere('description', 'like', $like))
            ->latest()
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (SystemDocumentation $d) => $this->make(
            'policy',
            $d->title,
            Str::limit(strip_tags((string) $d->description), 70) ?: 'University policy',
            route('dashboard.system-documentation.preview', $d->id),
            $d->file_type,
            optional($d->created_at)->format('M j, Y'),
        ));
    }

    private function manuals(string $like, int $perGroup): array
    {
        $rows = UserManual::where(fn ($w) => $w->where('title', 'like', $like)
            ->orWhere('description', 'like', $like))
            ->latest()
            ->limit($perGroup + 1)
            ->get();

        return $this->pack($rows, $perGroup, fn (UserManual $m) => $this->make(
            'manual',
            $m->title,
            Str::limit(strip_tags((string) $m->description), 70) ?: 'User manual',
            route('dashboard.user-manual.preview', $m->id),
            $m->file_type,
            optional($m->created_at)->format('M j, Y'),
        ));
    }

    /**
     * Static app pages / navigation targets. Each entry:
     *   [label, keywords, route-resolver, manager-only?]
     * Management pages are gated on $isManager and every route is guarded with
     * Route::has() so a missing route name can never blow up the header.
     */
    private function pages(string $q, bool $isManager, int $perGroup): array
    {
        $needle  = Str::lower($q);
        $compose = $isManager ? 'admin.communication.create'      : 'admin.communication-admin.create';
        $memos   = $isManager ? 'admin.communication.index'       : 'admin.communication-admin.index';
        $policy  = $isManager ? 'dashboard.system-documentation.manage' : 'dashboard.system-documentation';
        $manual  = $isManager ? 'dashboard.user-manual.manage'    : 'dashboard.user-manual';
        $comm    = $isManager ? 'committees.index'                : 'committees.my-committees';

        $defs = [
            ['Dashboard',              'home overview',                      'dashboard',                        false],
            ['My Profile',             'account me',                         'dashboard.profile',                false],
            ['All Documents',          'exams documents portfolio',          'dashboard.document',               false],
            ['All Files',              'files documents',                    'dashboard.all.files',              false],
            ['My Folders',             'folders',                            'dashboard.folders.index',          false],
            ['Departmental Folders',   'department shared folders',          'dashboard.departmental-folders',   false],
            ['All Exams',              'exams papers',                       'dashboard.all.exams',              false],
            ['Forms Portal',           'forms workflow requisition',         'admin.forms.portal',               false],
            ['All Forms',              'forms gallery new form',             'admin.forms.gallery',              false],
            ['Memos Portal',           'uimms memos messages inbox',         'dashboard.uimms.portal',           false],
            ['Keep in View',           'bookmarked memos',                   'dashboard.uimms.keep-in-view',     false],
            ['Compose Memo',           'new memo write message',             $compose,                           false],
            ['Memos',                  'memos communication campaigns',      $memos,                             false],
            ['University Policies',    'policies documentation rules',       $policy,                            false],
            ['User Manual',            'help guide manual how to',           $manual,                            false],
            ['Committees & Boards',    'committees boards cbms',             $comm,                              false],
            ['System Licences',        'licence license subscription',       'dashboard.system-licences',        false],
            ['Settings',               'preferences configuration account',  'dashboard.settings',               false],
            ['Payment History',        'payments invoices transactions',     'dashboard.payment-history.index',  true],
            ['Manage Users',           'users staff accounts people',        'dashboard.users',                  true],
            ['Department / Faculty',   'departments faculties',              'departments.index',                true],
            ['Positions',              'positions roles titles',             'positions.index',                  true],
            ['Offices',                'offices institutional',              'offices.index',                    true],
            ['System Letterheads',     'letterhead branding',                'dashboard.system-letterheads.index', true],
        ];

        $items = [];
        foreach ($defs as [$label, $keywords, $routeName, $managerOnly]) {
            if ($managerOnly && ! $isManager) {
                continue;
            }
            if (! Route::has($routeName)) {
                continue;
            }
            $hay = Str::lower($label . ' ' . $keywords);
            if (! str_contains($hay, $needle)) {
                continue;
            }
            $items[] = $this->make('page', $label, 'Go to ' . $label, route($routeName), null, null);
            if (count($items) > $perGroup) {
                break;
            }
        }

        return [
            'items'    => array_slice($items, 0, $perGroup),
            'has_more' => count($items) > $perGroup,
        ];
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Trim an over-fetched (perGroup+1) collection down to perGroup, mapping
     * each survivor through $mapper, and report whether more existed.
     */
    private function pack($rows, int $perGroup, callable $mapper): array
    {
        $hasMore = $rows->count() > $perGroup;

        return [
            'items'    => $rows->take($perGroup)->map($mapper)->values()->all(),
            'has_more' => $hasMore,
        ];
    }

    private function make(string $type, ?string $title, ?string $subtitle, string $url, ?string $badge = null, ?string $date = null): array
    {
        return [
            'type'     => $type,
            'title'    => (string) ($title ?? ''),
            'subtitle' => $subtitle !== null ? trim($subtitle) : '',
            'url'      => $url,
            'badge'    => $badge ? (string) $badge : null,
            'date'     => $date,
        ];
    }

    private function none(): array
    {
        return ['items' => [], 'has_more' => false];
    }
}
