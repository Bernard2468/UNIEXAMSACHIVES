{{--
    Chat thread on a submission.
    - $submission      : App\Models\FormSubmission
    - $comments        : pre-filtered collection (internal comments hidden where appropriate)
    - $canComment      : bool
    - $canViewInternal : bool  (controls whether the "internal" checkbox is shown)
--}}
<div class="form-panel">
    <div class="form-panel__head">
        <div>
            <h2 class="form-panel__title">Discussion<span class="form-panel__title-bar"></span></h2>
            <p class="form-panel__desc">Comments are visible to everyone in the form's signing chain.</p>
        </div>
    </div>
    <div class="form-panel__body">
        @if($comments->isEmpty())
            <p class="muted">No comments yet.</p>
        @else
            <ul class="comment-thread">
                @foreach($comments as $c)
                    <li class="comment {{ $c->is_internal ? 'comment--internal' : '' }}">
                        <div class="comment__head">
                            <span class="comment__author">{{ trim((optional($c->user)->first_name ?? '') . ' ' . (optional($c->user)->last_name ?? '')) }}</span>
                            <span class="comment__time">{{ $c->created_at->format('d M Y, H:i') }}</span>
                            @if($c->is_internal)
                                <span class="comment__badge">Internal</span>
                            @endif
                        </div>
                        <div class="comment__body">{!! nl2br(e($c->message)) !!}</div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($canComment)
            <form method="POST" action="{{ route('admin.forms.comment', $submission->id) }}" class="comment-form">
                @csrf
                <textarea name="message" rows="3" class="form-control" placeholder="Add a comment..." required maxlength="5000"></textarea>
                <div class="comment-form__actions">
                    @if($canViewInternal)
                        <label class="comment-form__internal">
                            <input type="checkbox" name="is_internal" value="1">
                            Internal (hidden from requisitioner)
                        </label>
                    @endif
                    <button type="submit" class="btn-action btn-action--primary">Post comment</button>
                </div>
            </form>
        @endif
    </div>
</div>

<style>
.muted { color: #9ca3af; font-size: 0.85rem; }
.comment-thread { list-style: none; padding: 0; margin: 0 0 14px; display: flex; flex-direction: column; gap: 8px; }
.comment { padding: 12px 14px; border-radius: 10px; background: #fafafa; border: 1.5px solid #ebebeb; }
.comment--internal { background: #fef3c7; border-color: #fde68a; }
.comment__head { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.74rem; color: #9ca3af; }
.comment__author { font-weight: 600; color: #111827; font-size: 0.84rem; }
.comment__time { font-size: 0.72rem; }
.comment__badge { font-size: 0.62rem; background: #92400e; color: #fff; padding: 2px 7px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; }
.comment__body { color: #374151; line-height: 1.5; font-size: 0.86rem; }
.comment-form { margin-top: 14px; }
.comment-form__actions { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; gap: 12px; flex-wrap: wrap; }
.comment-form__internal { font-size: 0.78rem; color: #6b7280; display: inline-flex; gap: 6px; align-items: center; cursor: pointer; margin: 0; }
.is_dark .comment { background: #0f172a; border-color: #1e2330; }
.is_dark .comment__author { color: #f3f4f6; }
.is_dark .comment__body { color: #d1d5db; }
</style>
