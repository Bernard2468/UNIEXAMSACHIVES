<?php

namespace App\Services\Forms;

use App\Forms\BaseFormDefinition;
use App\Forms\FormRegistry;
use App\Forms\FormStage;
use App\Mail\FormStageAssigned;
use App\Mail\FormSubmissionCompleted;
use App\Mail\FormSubmissionRejected;
use App\Models\FormAttachment;
use App\Models\FormComment;
use App\Models\FormSubmission;
use App\Models\Notification;
use App\Models\Office;
use App\Models\Position;
use App\Models\User;
use App\Services\Signing\SignatureService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Single mutator for every FormSubmission state transition.
 *
 * All controllers call into this service; nothing mutates submissions
 * directly. This is a security boundary: it guarantees that signature
 * chains, history, notifications and storage are written atomically and
 * that prior stage data can never be overwritten.
 */
class FormWorkflowService
{
    public function __construct(
        protected FormRegistry $registry,
        protected SignatureService $signatures,
    ) {
    }

    // ===========================================================
    // CREATION
    // ===========================================================

    /**
     * Create a new submission, save the requisitioner section data, and
     * either keep it as a draft or immediately sign + route to the first
     * downstream office.
     *
     * @param  array<string, mixed>      $requisitionerData
     * @param  array<int, UploadedFile>  $attachments
     * @param  array<string, mixed>      $signatureContext  ['signature_data'|'reuse_saved', 'ip', 'user_agent']
     */
    public function createSubmission(
        BaseFormDefinition $definition,
        User $creator,
        array $requisitionerData,
        array $attachments = [],
        ?string $action = 'send',
        ?int $nextAssigneeId = null,
        array $signatureContext = [],
        ?string $leadershipCategory = null,
        ?int $nextOfficeId = null,
    ): FormSubmission {
        return DB::transaction(function () use (
            $definition,
            $creator,
            $requisitionerData,
            $attachments,
            $action,
            $nextAssigneeId,
            $signatureContext,
            $leadershipCategory,
            $nextOfficeId
        ) {
            $firstStage = $definition->firstStage();

            $submission = new FormSubmission([
                'form_slug'   => $definition->slug(),
                'form_code'   => $definition->code(),
                'reference'   => $this->generateReference(),
                'title'       => $this->buildTitle($definition, $requisitionerData),
                'status'      => FormSubmission::STATUS_DRAFT,
                'created_by'  => $creator->id,
                'current_stage' => $firstStage->slug,
                'current_assignee_id' => $creator->id,
                'current_office_id'   => null,
                'priority'    => 'medium',
                'referred_to_vc' => false,
            ]);

            $submission->setSectionData($firstStage->slug, $requisitionerData);

            if ($amountField = $definition->amountFieldName()) {
                $amount = $requisitionerData[$amountField] ?? null;
                if (is_numeric($amount)) {
                    $submission->requisition_amount = (float) $amount;
                }
            }

            $submission->appendHistory('created', $creator->id, [
                'form_slug' => $definition->slug(),
                'reference' => $submission->reference,
            ]);

            $submission->save();

            $this->storeAttachments($submission, $firstStage->slug, $creator, $attachments);

            if ($action === 'send') {
                $this->signAndForward(
                    submission: $submission,
                    stageSlug: $firstStage->slug,
                    signer: $creator,
                    data: $requisitionerData,
                    signatureContext: $signatureContext,
                    nextAssigneeId: $nextAssigneeId,
                    leadershipCategory: $leadershipCategory,
                    nextOfficeId: $nextOfficeId,
                );
            }

            return $submission->fresh();
        });
    }

    // ===========================================================
    // STAGE-LEVEL OPERATIONS
    // ===========================================================

    /**
     * Persist current-stage data without forwarding (Save & continue later).
     * Used both for the requisitioner draft and for any office that wants
     * to part-fill before signing.
     *
     * @param  array<string, mixed>     $data
     * @param  array<int, UploadedFile> $attachments
     */
    public function saveStageData(
        FormSubmission $submission,
        string $stageSlug,
        User $user,
        array $data,
        array $attachments = []
    ): FormSubmission {
        $this->assertEditableStage($submission, $stageSlug, $user);

        return DB::transaction(function () use ($submission, $stageSlug, $user, $data, $attachments) {
            $submission->setSectionData($stageSlug, $data);
            $submission->appendHistory('stage_saved', $user->id, ['stage' => $stageSlug]);
            $submission->save();

            $this->storeAttachments($submission, $stageSlug, $user, $attachments);

            return $submission->fresh();
        });
    }

    /**
     * Sign the current stage, lock its data, and advance the form to the
     * next stage (or mark complete if there is none).
     *
     * @param  array<string, mixed> $data              Final stage data being signed.
     * @param  array<string, mixed> $signatureContext  ['signature_data'|'reuse_saved','ip','user_agent']
     * @param  ?int                 $nextAssigneeId    User to route to next (must belong to next stage's office).
     * @param  array<int, UploadedFile> $attachments
     */
    public function signAndForward(
        FormSubmission $submission,
        string $stageSlug,
        User $signer,
        array $data,
        array $signatureContext = [],
        ?int $nextAssigneeId = null,
        array $attachments = [],
        ?string $leadershipCategory = null,
        ?int $nextOfficeId = null,
    ): FormSubmission {
        $definition = $this->resolveDefinition($submission);
        $stage      = $this->resolveStage($definition, $stageSlug);

        $this->assertEditableStage($submission, $stageSlug, $signer);

        if ($stage->signatureRequired
            && empty($signatureContext['signature_data'])
            && empty($signatureContext['reuse_saved'])) {
            abort(422, 'A signature is required to forward this form.');
        }

        return DB::transaction(function () use (
            $submission,
            $signer,
            $definition,
            $stage,
            $data,
            $signatureContext,
            $nextAssigneeId,
            $attachments,
            $leadershipCategory,
            $nextOfficeId
        ) {
            $submission->setSectionData($stage->slug, $data);

            $this->storeAttachments($submission, $stage->slug, $signer, $attachments);

            if ($stage->signatureRequired) {
                $this->signatures->sign(
                    $submission,
                    $stage->slug,
                    $signer,
                    $data,
                    $signatureContext
                );
            }

            $vcField = $definition->vcReferralFieldName();
            $shouldReferToVc = false;
            if ($vcField && !empty($data[$vcField])) {
                $shouldReferToVc = (bool) $data[$vcField];
                if ($shouldReferToVc) {
                    $submission->referred_to_vc = true;
                }
            }

            $nextStage = $this->determineNextStage($definition, $stage, $shouldReferToVc);

            if (!$nextStage) {
                return $this->completeSubmission($submission, $signer);
            }

            $assignee = $this->resolveNextAssignee($nextStage, $nextAssigneeId, $leadershipCategory, $nextOfficeId);
            if (!$assignee) {
                if ($nextStage->isLeadershipPool()) {
                    abort(422, "No matching leadership user is available for this form. Ask an administrator to tag the appropriate positions as HOD / Dean / Director.");
                }
                if ($nextStage->isLeadershipOrOfficePool()) {
                    abort(422, "No matching recipient is available. Pick a Dean / HOD / Director, or an office whose head is active.");
                }
                abort(422, "No active member of the {$nextStage->label} office is available to receive this form. Ask your administrator to assign someone to that office.");
            }

            // Resolve the office record this stage now lives under, if any.
            // For LEADERSHIP_OR_OFFICE with category=office we use the picked office.
            // For POOL_OFFICE we use the stage's hard-wired office slug.
            $office = null;
            if ($nextStage->isLeadershipOrOfficePool() && $leadershipCategory === 'office' && $nextOfficeId) {
                $office = Office::find($nextOfficeId);
            } elseif ($nextStage->officeSlug) {
                $office = Office::where('slug', $nextStage->officeSlug)->first();
            }

            $submission->status              = FormSubmission::STATUS_IN_PROGRESS;
            $submission->current_stage       = $nextStage->slug;
            $submission->current_assignee_id = $assignee->id;
            $submission->current_office_id   = $office?->id;

            if ($stage->isRequisitionerStage() && !$submission->submitted_at) {
                $submission->submitted_at = now();
            }

            $submission->appendHistory('stage_signed', $signer->id, [
                'stage'             => $stage->slug,
                'next'              => $nextStage->slug,
                'assignee'          => $assignee->id,
                'office'            => $office?->slug,
                'leadership_category' => $nextStage->hasDynamicRecipient() ? $leadershipCategory : null,
                'vc_referral'       => $shouldReferToVc,
            ]);

            $submission->save();

            $this->notifyAssignee($submission, $assignee, $signer);

            return $submission->fresh();
        });
    }

    /**
     * Reject the current stage and send the form back to the requisitioner
     * (or to the prior signer if the current stage IS the requisitioner —
     * which we disallow because the requisitioner cannot reject themselves).
     */
    public function reject(FormSubmission $submission, User $user, string $reason): FormSubmission
    {
        $this->assertEditableStage($submission, $submission->current_stage, $user);

        if ($submission->current_stage === 'requisitioner') {
            abort(422, 'The requisitioner stage cannot be rejected.');
        }

        return DB::transaction(function () use ($submission, $user, $reason) {
            $submission->status              = FormSubmission::STATUS_REJECTED;
            $submission->rejected_at         = now();
            $submission->current_stage       = 'requisitioner';
            $submission->current_assignee_id = $submission->created_by;
            $submission->current_office_id   = null;

            $submission->appendHistory('rejected', $user->id, [
                'reason' => $reason,
                'from_stage' => $submission->getOriginal('current_stage'),
            ]);

            $submission->save();

            FormComment::create([
                'form_submission_id' => $submission->id,
                'user_id'            => $user->id,
                'message'            => "Form rejected: {$reason}",
                'is_internal'        => false,
            ]);

            Notification::create([
                'user_id'  => $submission->created_by,
                'actor_id' => $user->id,
                'type'     => 'form_rejected',
                'category' => Notification::CATEGORY_FORM,
                'title'    => "Your form was sent back ({$submission->reference})",
                'message'  => $reason,
                'url'      => route('admin.forms.show', $submission->id),
                'data'     => [
                    'submission_id' => $submission->id,
                    'form_code'     => $submission->form_code,
                    'reference'     => $submission->reference,
                ],
            ]);

            $this->sendEmail($submission->creator, new FormSubmissionRejected($submission, $reason));

            return $submission->fresh();
        });
    }

    /**
     * Mark the form as fully completed (no further stages).
     */
    public function completeSubmission(FormSubmission $submission, User $user): FormSubmission
    {
        $submission->status              = FormSubmission::STATUS_COMPLETED;
        $submission->completed_at        = now();
        $submission->current_stage       = null;
        $submission->current_assignee_id = null;
        $submission->current_office_id   = null;

        $submission->appendHistory('completed', $user->id, []);
        $submission->save();

        Notification::create([
            'user_id'  => $submission->created_by,
            'actor_id' => $user->id,
            'type'     => 'form_completed',
            'category' => Notification::CATEGORY_FORM,
            'title'    => "Your form is fully approved ({$submission->reference})",
            'message'  => 'All offices have signed your form.',
            'url'      => route('admin.forms.show', $submission->id),
            'data'     => [
                'submission_id' => $submission->id,
                'form_code'     => $submission->form_code,
                'reference'     => $submission->reference,
            ],
        ]);

        $this->sendEmail($submission->creator, new FormSubmissionCompleted($submission));

        return $submission->fresh();
    }

    /**
     * Reroute the currently-held stage to a different member of the same
     * office. Use case: the current assignee is on leave / unavailable and
     * the office head needs to keep the form moving.
     *
     * Constraints (re-asserted here even though the policy enforces them —
     * this method is the single-mutator and must be safe in isolation):
     *   - Status must be in_progress.
     *   - The form must currently sit on an office (current_office_id not null).
     *   - The new assignee must be an active member of that office.
     *   - The new assignee must NOT be the current assignee (no-op refused).
     *
     * Writes an audit entry, posts a public comment so the chain of custody is
     * visible, and notifies the new assignee.
     */
    public function reassign(
        FormSubmission $submission,
        User $newAssignee,
        User $by,
        string $reason
    ): FormSubmission {
        if ($submission->status !== FormSubmission::STATUS_IN_PROGRESS) {
            abort(422, 'Only in-progress forms can be reassigned.');
        }
        if (!$submission->current_office_id) {
            abort(422, 'This stage is not held by an office and cannot be reassigned this way.');
        }
        if ((int) $newAssignee->id === (int) $submission->current_assignee_id) {
            abort(422, 'That person already holds this form.');
        }

        $office = Office::find($submission->current_office_id);
        if (!$office) {
            abort(422, 'Current office could not be loaded.');
        }

        $isMember = $office->activeUsers()->where('users.id', $newAssignee->id)->exists();
        if (!$isMember) {
            abort(422, 'The selected recipient is not an active member of this office.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            abort(422, 'A reason for the reassignment is required.');
        }

        return DB::transaction(function () use ($submission, $newAssignee, $by, $reason) {
            $previousAssigneeId = $submission->current_assignee_id;

            $submission->current_assignee_id = $newAssignee->id;
            $submission->appendHistory('reassigned', $by->id, [
                'from_user' => $previousAssigneeId,
                'to_user'   => $newAssignee->id,
                'stage'     => $submission->current_stage,
                'reason'    => $reason,
            ]);
            $submission->save();

            FormComment::create([
                'form_submission_id' => $submission->id,
                'user_id'            => $by->id,
                'message'            => "Reassigned to {$newAssignee->first_name} {$newAssignee->last_name}: {$reason}",
                'is_internal'        => true,
            ]);

            Notification::create([
                'user_id'  => $newAssignee->id,
                'actor_id' => $by->id,
                'type'     => 'form_assigned',
                'category' => Notification::CATEGORY_FORM,
                'title'    => "Form reassigned to you ({$submission->reference})",
                'message'  => "{$by->first_name} {$by->last_name} reassigned this {$submission->form_code} form to you.",
                'url'      => route('admin.forms.show', $submission->id),
                'data'     => [
                    'submission_id' => $submission->id,
                    'form_code'     => $submission->form_code,
                    'reference'     => $submission->reference,
                    'reason'        => $reason,
                ],
            ]);

            $this->sendEmail($newAssignee, new FormStageAssigned($submission, $newAssignee, $by));

            return $submission->fresh();
        });
    }

    public function cancel(FormSubmission $submission, User $user): FormSubmission
    {
        $submission->status              = FormSubmission::STATUS_CANCELLED;
        $submission->current_assignee_id = null;
        $submission->current_office_id   = null;
        $submission->appendHistory('cancelled', $user->id, []);
        $submission->save();

        return $submission->fresh();
    }

    public function addComment(FormSubmission $submission, User $user, string $message, bool $isInternal = false): FormComment
    {
        return FormComment::create([
            'form_submission_id' => $submission->id,
            'user_id'            => $user->id,
            'message'            => trim($message),
            'is_internal'        => $isInternal,
        ]);
    }

    // ===========================================================
    // HELPERS
    // ===========================================================

    /**
     * Defends against tampered HTML: ensures the user is the current
     * assignee for the named stage and the submission is still mutable.
     */
    protected function assertEditableStage(FormSubmission $submission, ?string $stageSlug, User $user): void
    {
        if (!$stageSlug || $submission->current_stage !== $stageSlug) {
            abort(403, 'This stage is no longer active for this form.');
        }

        if (!in_array($submission->status, [
            FormSubmission::STATUS_DRAFT,
            FormSubmission::STATUS_IN_PROGRESS,
        ], true)) {
            abort(403, 'This form is no longer editable.');
        }

        if ((int) $submission->current_assignee_id !== (int) $user->id) {
            abort(403, 'You are not the current assignee for this form.');
        }
    }

    protected function resolveDefinition(FormSubmission $submission): BaseFormDefinition
    {
        $definition = $this->registry->find($submission->form_slug);
        if (!$definition) {
            abort(500, "Form definition [{$submission->form_slug}] is missing.");
        }
        return $definition;
    }

    protected function resolveStage(BaseFormDefinition $definition, string $stageSlug): FormStage
    {
        $stage = $definition->stage($stageSlug);
        if (!$stage) {
            abort(404, "Unknown stage [{$stageSlug}] on form [{$definition->slug()}].");
        }
        return $stage;
    }

    protected function determineNextStage(BaseFormDefinition $definition, FormStage $stage, bool $referToVc): ?FormStage
    {
        if ($referToVc && in_array('vc', $stage->branches, true)) {
            return $definition->stage('vc');
        }

        return $definition->nextStageAfter($stage->slug, includeOptional: false);
    }

    protected function resolveNextAssignee(
        FormStage $stage,
        ?int $nextAssigneeId,
        ?string $leadershipCategory = null,
        ?int $nextOfficeId = null,
    ): ?User
    {
        // ── Leadership pool: HOD / Dean / Director picked dynamically ──
        if ($stage->isLeadershipPool()) {
            return $this->resolveLeadershipAssignee($nextAssigneeId, $leadershipCategory);
        }

        // ── Leadership-OR-office pool (Annual Leave recommender stage) ──
        if ($stage->isLeadershipOrOfficePool()) {
            if (!$leadershipCategory) {
                abort(422, 'Please choose Dean, HOD, Director or Office for the recommender.');
            }
            if ($leadershipCategory === 'office') {
                if (!$nextOfficeId) {
                    abort(422, 'Please pick the office whose head will recommend this application.');
                }
                $office = Office::find($nextOfficeId);
                if (!$office || !$office->is_active) {
                    abort(422, 'The selected office is not available. Refresh the page and pick again.');
                }
                $head = $office->head();
                if (!$head) {
                    abort(422, "{$office->name} has no active head. Pick another office or ask an administrator to designate a head.");
                }
                // Cross-check against the assignee id submitted by the form (which
                // the client mirrors from the office's head). This prevents the
                // form from silently routing to a stale user if the head changes
                // between page load and submit.
                if ($nextAssigneeId && (int) $nextAssigneeId !== (int) $head->id) {
                    // Not a hard error — trust the server-resolved head over a
                    // possibly-stale client-side hint. Just log it.
                    Log::info('Form recommender office head differs from client hint', [
                        'office_id'   => $office->id,
                        'client_hint' => $nextAssigneeId,
                        'server_head' => $head->id,
                    ]);
                }
                return $head;
            }
            // Leadership branch within the flexible pool — same as POOL_LEADERSHIP.
            return $this->resolveLeadershipAssignee($nextAssigneeId, $leadershipCategory);
        }

        // ── Office pool (default) ──
        if (!$stage->officeSlug) {
            // No office and not a leadership pool means it's the requisitioner —
            // caller is responsible for setting current_assignee_id.
            return $nextAssigneeId ? User::find($nextAssigneeId) : null;
        }

        $office = Office::where('slug', $stage->officeSlug)->first();
        if (!$office) {
            return null;
        }

        if ($nextAssigneeId) {
            $user = $office->activeUsers()->where('users.id', $nextAssigneeId)->first();
            if ($user) {
                return $user;
            }
            // If the picked user is not in the target office, refuse to silently
            // misroute the form.
            abort(422, 'Selected recipient is not an active member of the next office.');
        }

        return $office->head() ?? $office->activeUsers()->first();
    }

    /**
     * Resolve a user from the leadership pool (HOD/Dean/Director) by id
     * and category, asserting the user actually holds that category.
     */
    protected function resolveLeadershipAssignee(?int $nextAssigneeId, ?string $leadershipCategory): User
    {
        if (!$nextAssigneeId) {
            abort(422, 'Please pick the specific Dean / HOD / Director who should receive this form.');
        }
        if (!$leadershipCategory || !in_array($leadershipCategory, array_keys(Position::CATEGORIES), true)) {
            abort(422, 'Please choose whether this form is going to a Dean, HOD or Director.');
        }

        $user = User::query()
            ->where('id', $nextAssigneeId)
            ->whereHas('position', fn ($q) => $q->where('category', $leadershipCategory))
            ->first();

        if (!$user) {
            abort(422, 'The selected recipient is not tagged as a ' . Position::CATEGORIES[$leadershipCategory] . '. Refresh the page and pick again.');
        }

        return $user;
    }

    /**
     * Persist uploaded files to the public disk under form-attachments/{id}/.
     *
     * @param  array<int, UploadedFile> $attachments
     */
    protected function storeAttachments(FormSubmission $submission, string $stageSlug, User $user, array $attachments): void
    {
        foreach ($attachments as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $path = $file->store("form-attachments/{$submission->id}", 'public');

            FormAttachment::create([
                'form_submission_id' => $submission->id,
                'stage_slug'         => $stageSlug,
                'name'               => $file->getClientOriginalName(),
                'path'               => $path,
                'mime_type'          => $file->getClientMimeType(),
                'size'               => $file->getSize(),
                'uploaded_by'        => $user->id,
            ]);
        }
    }

    protected function notifyAssignee(FormSubmission $submission, User $assignee, User $sender): void
    {
        Notification::create([
            'user_id'  => $assignee->id,
            'actor_id' => $sender->id,
            'type'     => 'form_assigned',
            'category' => Notification::CATEGORY_FORM,
            'title'    => "Form awaiting your action ({$submission->reference})",
            'message'  => "{$sender->first_name} {$sender->last_name} forwarded a {$submission->form_code} form to you.",
            'url'      => route('admin.forms.show', $submission->id),
            'data'     => [
                'submission_id' => $submission->id,
                'form_code'     => $submission->form_code,
                'reference'     => $submission->reference,
                'stage'         => $submission->current_stage,
            ],
        ]);

        $this->sendEmail($assignee, new FormStageAssigned($submission, $assignee, $sender));
    }

    /**
     * Email failures must not break the workflow — we log and continue.
     */
    protected function sendEmail(?User $user, $mailable): void
    {
        if (!$user || empty($user->email)) {
            return;
        }

        try {
            Mail::to($user->email)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Form mail send failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'mailable' => get_class($mailable),
            ]);
        }
    }

    /**
     * Reference generator — FRM-CUG#### style, matching memo's REF-CUG#### pattern.
     */
    protected function generateReference(): string
    {
        do {
            $code = 'FRM-CUG' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (FormSubmission::where('reference', $code)->exists());

        return $code;
    }

    protected function buildTitle(BaseFormDefinition $definition, array $requisitionerData): string
    {
        $brief = $requisitionerData['brief_payment_request']
            ?? $requisitionerData['purchase_description']
            ?? $requisitionerData['purpose']
            ?? null;

        if ($brief) {
            return Str::limit(trim(strip_tags((string) $brief)), 80);
        }

        return $definition->title();
    }
}
