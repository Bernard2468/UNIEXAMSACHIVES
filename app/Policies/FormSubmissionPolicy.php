<?php

namespace App\Policies;

use App\Models\FormSubmission;
use App\Models\User;

/**
 * Single source of truth for who may do what with a form submission.
 *
 * Visibility rule (any of):
 *   - User is the creator
 *   - User is the current assignee
 *   - User has already signed a stage on this submission
 *   - User is an active member of the office currently holding the form
 *   - User is super admin
 *
 * Edit / sign rule:
 *   - User must be the current assignee AND submission must be in_progress.
 *
 * Comment rule:
 *   - Same as visibility — anyone in the chain can comment.
 *
 * Cancel rule:
 *   - Only the creator while the form is still draft or in_progress AND has not
 *     yet been signed by anyone beyond the requisitioner stage.
 */
class FormSubmissionPolicy
{
    /**
     * Hook called for every check — super admin always passes.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function view(User $user, FormSubmission $submission): bool
    {
        if ((int) $submission->created_by === (int) $user->id) {
            return true;
        }

        if ((int) ($submission->current_assignee_id ?? 0) === (int) $user->id) {
            return true;
        }

        if ($submission->signatures()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($submission->current_office_id) {
            $isMember = $user->activeOffices()
                ->where('offices.id', $submission->current_office_id)
                ->exists();
            if ($isMember) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the user may fill / save the current stage's section data.
     */
    public function fillCurrentStage(User $user, FormSubmission $submission): bool
    {
        return $submission->status === FormSubmission::STATUS_IN_PROGRESS
            && (int) ($submission->current_assignee_id ?? 0) === (int) $user->id;
    }

    /**
     * Whether the user may sign + forward the current stage.
     * Alias of fillCurrentStage for clarity at call sites.
     */
    public function sign(User $user, FormSubmission $submission): bool
    {
        return $this->fillCurrentStage($user, $submission);
    }

    /**
     * Whether the user may reject / send the form back from the current stage.
     */
    public function reject(User $user, FormSubmission $submission): bool
    {
        return $this->fillCurrentStage($user, $submission);
    }

    /**
     * Whether the user may comment on the submission.
     */
    public function comment(User $user, FormSubmission $submission): bool
    {
        return $this->view($user, $submission);
    }

    /**
     * Internal comments are visible to anyone in the chain EXCEPT the
     * requisitioner (who must not see private staff deliberations).
     */
    public function viewInternalComments(User $user, FormSubmission $submission): bool
    {
        if (!$this->view($user, $submission)) {
            return false;
        }
        return (int) $submission->created_by !== (int) $user->id;
    }

    /**
     * Whether the user may re-route the current stage to a different
     * recipient (typically because the current assignee is on leave).
     *
     * Allowed for:
     *   - The head of the office currently holding the form (office-pool stages).
     *   - Super admin (handled by before()).
     *
     * Not allowed for:
     *   - Leadership-pool stages where current_office_id is null — the
     *     requisitioner picked the specific person, only super admin can
     *     change that decision.
     *   - The current assignee themselves — they should reject the form back
     *     with a reason instead.
     */
    public function reassign(User $user, FormSubmission $submission): bool
    {
        if ($submission->status !== FormSubmission::STATUS_IN_PROGRESS) {
            return false;
        }
        if (!$submission->current_office_id) {
            return false;
        }
        if ((int) $submission->current_assignee_id === (int) $user->id) {
            return false;
        }

        $isHeadOfCurrentOffice = $user->activeOffices()
            ->where('offices.id', $submission->current_office_id)
            ->wherePivot('is_head', true)
            ->exists();

        return $isHeadOfCurrentOffice;
    }

    public function cancel(User $user, FormSubmission $submission): bool
    {
        if ((int) $submission->created_by !== (int) $user->id) {
            return false;
        }

        if (!in_array($submission->status, [
            FormSubmission::STATUS_DRAFT,
            FormSubmission::STATUS_IN_PROGRESS,
        ], true)) {
            return false;
        }

        // Once any post-requisitioner stage has been signed, the creator cannot
        // unilaterally cancel — they must request rejection from the current office.
        $beyondRequisitioner = $submission->signatures()
            ->where('stage_slug', '!=', 'requisitioner')
            ->exists();

        return !$beyondRequisitioner;
    }

    public function downloadPdf(User $user, FormSubmission $submission): bool
    {
        return $this->view($user, $submission);
    }

    public function downloadAttachment(User $user, FormSubmission $submission): bool
    {
        return $this->view($user, $submission);
    }
}
