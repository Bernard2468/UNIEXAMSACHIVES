{{--
    Notice shown when the next stage uses POOL_CREATOR — the form returns to
    the original applicant for a re-confirmation step (e.g. the Renewal of
    Appointment declaration). No picker; just a friendly explanation of who's
    receiving it next.

    Vars:
      - $recipient : App\Models\User|null — the applicant the form will return to.
      - $nextStage : App\Forms\FormStage  — used for its description text.
--}}
@php
    $recipient = $recipient ?? null;
    $fullName  = $recipient ? trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? '')) : null;
    $initials  = $recipient ? strtoupper(substr($recipient->first_name ?? '', 0, 1) . substr($recipient->last_name ?? '', 0, 1)) : '?';
@endphp

<div class="creator-notice">
    <div class="creator-notice__icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 14l-4-4 4-4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/>
        </svg>
    </div>
    <div class="creator-notice__body">
        <div class="creator-notice__title">Returns to the applicant</div>
        @if($recipient)
            <div class="creator-notice__card">
                <div class="creator-notice__avatar">
                    @if(!empty($recipient->profile_picture))
                        <img src="{{ asset('profile_pictures/' . $recipient->profile_picture) }}" alt="{{ $fullName }}">
                    @else
                        <span>{{ $initials }}</span>
                    @endif
                </div>
                <div class="creator-notice__meta">
                    <div class="creator-notice__name">{{ $fullName ?: 'Applicant' }}</div>
                    @if($recipient->email)
                        <div class="creator-notice__email">{{ $recipient->email }}</div>
                    @endif
                </div>
            </div>
        @endif
        <p class="creator-notice__hint">
            Once you sign and forward, the form goes back to the applicant for their declaration (Section 8). They'll review your comments above and then forward it on to the Registrar.
        </p>
    </div>
</div>

<style>
.creator-notice { display: flex; align-items: flex-start; gap: 14px; padding: 16px; background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 12px; font-family: 'Outfit', sans-serif !important; }
.creator-notice * { box-sizing: border-box; }
.creator-notice__icon { width: 38px; height: 38px; border-radius: 10px; background: #fff; border: 1.5px solid #e5e7eb; color: #0c0c0c; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.creator-notice__body { flex: 1; min-width: 0; }
.creator-notice__title { font-weight: 700; color: #0c0c0c; font-size: 0.92rem; letter-spacing: -0.01em; margin-bottom: 8px; }
.creator-notice__card { display: inline-flex; align-items: center; gap: 10px; padding: 7px 12px 7px 8px; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 99px; margin-bottom: 10px; }
.creator-notice__avatar { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #0c0c0c, #374151); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.72rem; flex-shrink: 0; overflow: hidden; }
.creator-notice__avatar img { width: 100%; height: 100%; object-fit: cover; }
.creator-notice__meta { min-width: 0; }
.creator-notice__name { font-weight: 600; color: #111827; font-size: 0.84rem; line-height: 1.1; }
.creator-notice__email { color: #9ca3af; font-size: 0.72rem; margin-top: 2px; }
.creator-notice__hint { margin: 0; color: #6b7280; font-size: 0.8rem; line-height: 1.55; }

.is_dark .creator-notice { background: #0b1322; border-color: #1e2330; }
.is_dark .creator-notice__icon { background: #111827; border-color: #2d3748; color: #f3f4f6; }
.is_dark .creator-notice__title { color: #f3f4f6; }
.is_dark .creator-notice__card { background: #111827; border-color: #2d3748; }
.is_dark .creator-notice__name { color: #f3f4f6; }
.is_dark .creator-notice__hint { color: #9ca3af; }
</style>
