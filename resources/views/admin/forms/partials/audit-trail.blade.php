{{--
    Workflow audit trail — renders the append-only history captured on
    FormSubmission->workflow_history. Read-only, simple timeline.

    Vars:
      - $submission : App\Models\FormSubmission
--}}
@php
    use App\Forms\FormStage;

    $events = collect($submission->workflow_history ?? []);
    if ($events->isEmpty()) {
        return;
    }

    $definition = $submission->definition();
    $userMap = \App\Models\User::query()
        ->whereIn('id', $events->pluck('user_id')->filter()->unique()->values())
        ->get(['id', 'first_name', 'last_name', 'profile_picture'])
        ->keyBy('id');

    $describe = function (array $event) use ($definition, $userMap) {
        $action  = $event['action']  ?? 'unknown';
        $details = $event['details'] ?? [];

        $titleByAction = [
            'created'      => 'Form created',
            'stage_saved'  => 'Stage saved (draft)',
            'stage_signed' => 'Stage signed & forwarded',
            'reassigned'   => 'Reassigned to another office member',
            'rejected'     => 'Sent back to requisitioner',
            'completed'    => 'Form completed',
            'cancelled'    => 'Form cancelled',
        ];
        $title = $titleByAction[$action] ?? ucfirst(str_replace('_', ' ', $action));

        $stageLabel = function (?string $slug) use ($definition) {
            if (!$slug || !$definition) return $slug;
            $stage = $definition->stage($slug);
            return $stage?->label ?? $slug;
        };

        $sub = null;
        if ($action === 'stage_signed') {
            $from = $stageLabel($details['stage'] ?? null);
            $to   = $stageLabel($details['next']  ?? null);
            $sub  = $from . ' → ' . $to;
            $cat  = $details['leadership_category'] ?? null;
            if ($cat && isset(\App\Models\Position::CATEGORIES[$cat])) {
                $sub .= '  ·  ' . \App\Models\Position::CATEGORIES[$cat];
            }
        } elseif ($action === 'stage_saved') {
            $sub = $stageLabel($details['stage'] ?? null);
        } elseif ($action === 'reassigned') {
            $from = $userMap[$details['from_user'] ?? null] ?? null;
            $to   = $userMap[$details['to_user']   ?? null] ?? null;
            $fromName = $from ? trim(($from->first_name ?? '') . ' ' . ($from->last_name ?? '')) : '—';
            $toName   = $to   ? trim(($to->first_name   ?? '') . ' ' . ($to->last_name   ?? '')) : '—';
            $sub      = "{$fromName} → {$toName}  ·  " . trim((string) ($details['reason'] ?? '—'));
        } elseif ($action === 'rejected') {
            $sub = 'Reason: ' . trim((string) ($details['reason'] ?? '—'));
        } elseif ($action === 'created') {
            $sub = 'Reference ' . ($details['reference'] ?? '');
        }

        return ['title' => $title, 'sub' => $sub, 'action' => $action];
    };
@endphp

<div class="form-panel audit-trail-panel">
    <div class="form-panel__head">
        <div>
            <h2 class="form-panel__title">Activity<span class="form-panel__title-bar"></span></h2>
            <p class="form-panel__desc">Append-only audit of every action taken on this form.</p>
        </div>
    </div>
    <div class="form-panel__body">
        <ol class="audit-timeline">
            @foreach($events as $event)
                @php
                    $info = $describe($event);
                    $user = $userMap[$event['user_id'] ?? null] ?? null;
                    $when = isset($event['timestamp']) ? \Illuminate\Support\Carbon::parse($event['timestamp']) : null;
                    $initials = $user
                        ? strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1))
                        : '?';
                    $fullName = $user
                        ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                        : 'System';
                @endphp
                <li class="audit-timeline__item audit-timeline__item--{{ $info['action'] }}">
                    <div class="audit-timeline__rail">
                        <span class="audit-timeline__dot"></span>
                    </div>
                    <div class="audit-timeline__card">
                        <div class="audit-timeline__hd">
                            <span class="audit-timeline__avatar">
                                @if($user && !empty($user->profile_picture))
                                    <img src="{{ asset('profile_pictures/' . $user->profile_picture) }}" alt="{{ $fullName }}">
                                @else
                                    <span>{{ $initials }}</span>
                                @endif
                            </span>
                            <div class="audit-timeline__meta">
                                <div class="audit-timeline__title">{{ $info['title'] }}</div>
                                <div class="audit-timeline__by">
                                    by <strong>{{ $fullName }}</strong>
                                    @if($when)
                                        · <span title="{{ $when->toDayDateTimeString() }}">{{ $when->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($info['sub'])
                            <div class="audit-timeline__sub">{{ $info['sub'] }}</div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>

<style>
.audit-trail-panel { font-family: 'Outfit', sans-serif !important; }
.audit-timeline { list-style: none; margin: 0; padding: 0; }
.audit-timeline__item { display: flex; gap: 14px; padding: 0; margin: 0; position: relative; }
.audit-timeline__rail { width: 18px; flex-shrink: 0; display: flex; flex-direction: column; align-items: center; padding-top: 16px; }
.audit-timeline__rail::before { content: ''; position: absolute; top: 0; bottom: 0; left: 8.5px; width: 1.5px; background: #ebebeb; }
.audit-timeline__item:first-child .audit-timeline__rail::before { top: 16px; }
.audit-timeline__item:last-child .audit-timeline__rail::before { bottom: calc(100% - 24px); }
.audit-timeline__dot { width: 9px; height: 9px; border-radius: 50%; background: #0c0c0c; border: 2px solid #fff; box-shadow: 0 0 0 1.5px #ebebeb; position: relative; z-index: 1; margin-top: 7px; }
.audit-timeline__item--rejected .audit-timeline__dot { background: #dc2626; }
.audit-timeline__item--completed .audit-timeline__dot { background: #059669; }
.audit-timeline__item--cancelled .audit-timeline__dot { background: #9ca3af; }

.audit-timeline__card { flex: 1; padding: 12px 0 18px; min-width: 0; }
.audit-timeline__hd { display: flex; align-items: center; gap: 10px; }
.audit-timeline__avatar { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #0c0c0c, #374151); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.66rem; flex-shrink: 0; overflow: hidden; }
.audit-timeline__avatar img { width: 100%; height: 100%; object-fit: cover; }
.audit-timeline__meta { flex: 1; min-width: 0; }
.audit-timeline__title { font-weight: 600; color: #111827; font-size: 0.86rem; letter-spacing: -0.01em; line-height: 1.2; }
.audit-timeline__by { color: #9ca3af; font-size: 0.74rem; margin-top: 2px; }
.audit-timeline__by strong { color: #6b7280; font-weight: 600; }
.audit-timeline__sub { margin: 6px 0 0 38px; padding: 8px 12px; background: #fafafa; border: 1.5px solid #f0f1f3; border-radius: 8px; font-size: 0.78rem; color: #6b7280; line-height: 1.5; word-break: break-word; }

.is_dark .audit-timeline__rail::before { background: #1e2330; }
.is_dark .audit-timeline__dot { background: #f3f4f6; border-color: #111827; box-shadow: 0 0 0 1.5px #1e2330; }
.is_dark .audit-timeline__title { color: #f3f4f6; }
.is_dark .audit-timeline__by strong { color: #d1d5db; }
.is_dark .audit-timeline__sub { background: #0f172a; border-color: #1e2330; color: #9ca3af; }
</style>
