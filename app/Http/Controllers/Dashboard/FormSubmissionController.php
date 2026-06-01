<?php

namespace App\Http\Controllers\Dashboard;

use App\Forms\FormField;
use App\Forms\FormRegistry;
use App\Forms\FormStage;
use App\Http\Controllers\Controller;
use App\Models\FormAttachment;
use App\Models\FormSubmission;
use App\Models\Office;
use App\Models\Position;
use App\Models\User;
use App\Services\Forms\FormWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * All user-facing form actions.
 *
 * Every action that touches a FormSubmission first runs an authorization
 * check via FormSubmissionPolicy and only mutates state through
 * FormWorkflowService — so no controller code bypasses the security model.
 */
class FormSubmissionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected FormRegistry $registry,
        protected FormWorkflowService $workflow,
    ) {
    }

    // ===========================================================
    // GALLERY + COMPOSE
    // ===========================================================

    /**
     * Gallery of available form types ("All Forms").
     */
    public function gallery()
    {
        $forms = $this->registry->all();
        return view('admin.forms.index', compact('forms'));
    }

    /**
     * Render the compose page for a chosen form type (stage 1 = requisitioner).
     */
    public function compose(string $formSlug)
    {
        $definition = $this->registry->findOrFail($formSlug);
        $stage      = $definition->firstStage();

        // The next stage after the requisitioner determines who we route to.
        $nextStage   = $definition->nextStageAfter($stage->slug);
        $nextContext = $this->buildNextStageContext($nextStage);

        $user = Auth::user();

        return view('admin.forms.compose', [
            'definition'           => $definition,
            'stage'                => $stage,
            'nextStage'            => $nextStage,
            'nextOffice'           => $nextContext['office'],
            'leadershipCandidates' => $nextContext['leadership'],
            'allOffices'           => $nextContext['all_offices'],
            // For POOL_CREATOR stages, the "creator" is the form's applicant.
            // On the initial compose page that's always the current user.
            'creatorRecipient'     => $nextStage && $nextStage->isCreatorPool() ? $user : null,
            'submission'           => null,
            'sectionData'          => $this->prefillRequisitioner($user, $stage),
            'savedSignature'       => $user->savedSignature,
        ]);
    }

    /**
     * Create a new submission from the requisitioner stage.
     */
    public function store(Request $request, string $formSlug)
    {
        $definition = $this->registry->findOrFail($formSlug);
        $stage      = $definition->firstStage();

        $action = $request->input('action') === 'draft' ? 'draft' : 'send';
        $data   = $this->validateStageInput($request, $definition, $stage, requireSignature: $action === 'send');

        $nextAssigneeId       = $action === 'send' ? $this->validatedNextAssignee($request, $definition, $stage) : null;
        $leadershipCategory   = $action === 'send' ? $this->extractLeadershipCategory($request) : null;
        $nextOfficeId         = $action === 'send' ? $this->extractNextOfficeId($request) : null;

        // Passport photo (forms that opt in via requiresPassportPhoto()) is a
        // dedicated file input on the compose page. We prepend it to the
        // attachments array so it's the first FormAttachment row created —
        // the PDF view picks "the first image at applicant stage" as the
        // passport photograph to embed in the top-right of page 1.
        //
        // Acceptance is lenient: a file counts as an image if EITHER the
        // detected mime type starts with image/ OR the original filename has
        // a common image extension. Some shared-hosting PHP builds report
        // empty / generic mime types for uploads, so the extension fallback
        // is essential. Rejections are logged so a misconfigured upload (size
        // limit hit, php-fileinfo missing, etc.) is visible in the log.
        $attachments = $request->file('attachments', []);
        if ($definition->requiresPassportPhoto()) {
            $photo = $request->file('passport_photo');
            if ($photo) {
                if (!$photo->isValid()) {
                    \Illuminate\Support\Facades\Log::warning('EPR passport photo upload error', [
                        'php_upload_error' => $photo->getError(),
                        'original_name'    => $photo->getClientOriginalName(),
                        'size'             => $photo->getSize(),
                        'submission_form'  => $definition->slug(),
                    ]);
                } else {
                    $mime = strtolower((string) ($photo->getMimeType() ?? ''));
                    $ext  = strtolower((string) $photo->getClientOriginalExtension());
                    $isImage = ($mime !== '' && str_starts_with($mime, 'image/'))
                            || in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);

                    if ($isImage) {
                        array_unshift($attachments, $photo);
                    } else {
                        \Illuminate\Support\Facades\Log::warning('EPR passport photo rejected — not an image', [
                            'mime'          => $mime,
                            'ext'           => $ext,
                            'original_name' => $photo->getClientOriginalName(),
                        ]);
                    }
                }
            }
        }

        $submission = $this->workflow->createSubmission(
            definition: $definition,
            creator: Auth::user(),
            requisitionerData: $data['fields'],
            attachments: $attachments,
            action: $action,
            nextAssigneeId: $nextAssigneeId,
            signatureContext: $this->signatureContext($request),
            leadershipCategory: $leadershipCategory,
            nextOfficeId: $nextOfficeId,
        );

        return redirect()->route('admin.forms.show', $submission->id)
            ->with('success', $action === 'send'
                ? "Form {$submission->reference} forwarded successfully."
                : "Draft {$submission->reference} saved.");
    }

    // ===========================================================
    // SHOW + STAGE-LEVEL OPS
    // ===========================================================

    public function show(FormSubmission $submission)
    {
        $this->authorize('view', $submission);

        $submission->load(['signatures.user', 'attachments.uploader', 'comments.user', 'creator', 'currentAssignee', 'currentOffice']);

        $definition = $submission->definition();
        abort_unless($definition, 500, 'Form definition missing.');

        $user = Auth::user();
        $canViewInternal = app(\App\Policies\FormSubmissionPolicy::class)->viewInternalComments($user, $submission);

        $comments = $submission->comments;
        if (!$canViewInternal) {
            $comments = $comments->where('is_internal', false)->values();
        }

        // If the current user is the active assignee, prepare the next-office picker.
        $nextOffice = null;
        $currentStage = $submission->current_stage ? $definition->stage($submission->current_stage) : null;
        $canFill = $currentStage
            ? app(\App\Policies\FormSubmissionPolicy::class)->fillCurrentStage($user, $submission)
            : false;

        $nextContext = ['office' => null, 'leadership' => null, 'all_offices' => collect()];
        $vcOffice    = null;

        if ($canFill && $currentStage) {
            $nextStage = $definition->nextStageAfter($currentStage->slug);
            $nextContext = $this->buildNextStageContext($nextStage);

            // For VC branch we also expose the VC office.
            $vcStage = in_array('vc', $currentStage->branches, true) ? $definition->stage('vc') : null;
            $vcOffice = $vcStage && $vcStage->officeSlug
                ? Office::with(['users' => fn ($q) => $q->wherePivot('is_active', true)])
                    ->where('slug', $vcStage->officeSlug)->first()
                : null;
        }

        return view('admin.forms.show', [
            'submission'           => $submission,
            'definition'           => $definition,
            'currentStage'         => $currentStage,
            'nextStage'            => $nextStage ?? null,
            'nextOffice'           => $nextContext['office'],
            'leadershipCandidates' => $nextContext['leadership'],
            'allOffices'           => $nextContext['all_offices'],
            // POOL_CREATOR routes back to the original applicant (the form's creator).
            'creatorRecipient'     => isset($nextStage) && $nextStage->isCreatorPool()
                                        ? $submission->creator
                                        : null,
            'vcOffice'             => $vcOffice,
            'canFill'              => $canFill,
            'canComment'           => app(\App\Policies\FormSubmissionPolicy::class)->comment($user, $submission),
            'canCancel'            => app(\App\Policies\FormSubmissionPolicy::class)->cancel($user, $submission),
            'canReassign'          => app(\App\Policies\FormSubmissionPolicy::class)->reassign($user, $submission),
            'reassignCandidates'   => app(\App\Policies\FormSubmissionPolicy::class)->reassign($user, $submission)
                                        ? Office::find($submission->current_office_id)
                                              ?->activeUsers()
                                              ->where('users.id', '!=', $submission->current_assignee_id)
                                              ->orderBy('users.first_name')
                                              ->get(['users.id', 'users.first_name', 'users.last_name', 'users.email'])
                                        : collect(),
            'canViewInternal'      => $canViewInternal,
            'comments'             => $comments,
            'savedSignature'       => $user->savedSignature,
        ]);
    }

    /**
     * Build the data the recipient picker needs for the given next stage:
     *  - office candidates when the stage routes to an Office
     *  - a category-keyed map of User collections for leadership pools
     *  - the full list of active offices for leadership-or-office pools
     */
    protected function buildNextStageContext(?FormStage $nextStage): array
    {
        $office     = null;
        $leadership = null;
        $allOffices = collect();

        if (!$nextStage) {
            return ['office' => null, 'leadership' => null, 'all_offices' => $allOffices];
        }

        if ($nextStage->isLeadershipPool() || $nextStage->isLeadershipOrOfficePool()) {
            $leadership = [];
            foreach (Position::CATEGORIES as $key => $_label) {
                $positionIds = Position::query()->where('category', $key)->pluck('id');
                if ($positionIds->isEmpty()) {
                    $leadership[$key] = collect();
                    continue;
                }
                $leadership[$key] = User::query()
                    ->whereIn('position_id', $positionIds)
                    ->orderBy('first_name')
                    ->get(['id', 'first_name', 'last_name', 'email', 'profile_picture', 'position_id', 'department_id']);
            }
        }

        if ($nextStage->isLeadershipOrOfficePool()) {
            $allOffices = Office::query()
                ->active()
                ->with(['users' => fn ($q) => $q->wherePivot('is_active', true)])
                ->orderBy('name')
                ->get();
        } elseif ($nextStage->officeSlug) {
            $office = Office::with(['users' => fn ($q) => $q->wherePivot('is_active', true)])
                ->where('slug', $nextStage->officeSlug)
                ->first();
        }

        return [
            'office'      => $office,
            'leadership'  => $leadership,
            'all_offices' => $allOffices,
        ];
    }

    /**
     * Save current-stage data without forwarding (Save Draft).
     */
    public function saveDraft(Request $request, FormSubmission $submission)
    {
        $this->authorize('fillCurrentStage', $submission);

        $definition = $submission->definition();
        $stage      = $definition->stage($submission->current_stage);
        abort_unless($stage, 404);

        $data = $this->validateStageInput($request, $definition, $stage, requireSignature: false);

        $this->workflow->saveStageData(
            $submission,
            $stage->slug,
            Auth::user(),
            $data['fields'],
            $request->file('attachments', []),
        );

        return redirect()->route('admin.forms.show', $submission->id)
            ->with('success', 'Draft saved.');
    }

    /**
     * Sign the current stage and forward to the next office.
     */
    public function sign(Request $request, FormSubmission $submission)
    {
        $this->authorize('sign', $submission);

        $definition = $submission->definition();
        $stage      = $definition->stage($submission->current_stage);
        abort_unless($stage, 404);

        $data = $this->validateStageInput($request, $definition, $stage, requireSignature: true);

        $nextAssigneeId     = $this->validatedNextAssignee($request, $definition, $stage, $data['fields']);
        $leadershipCategory = $this->extractLeadershipCategory($request);
        $nextOfficeId       = $this->extractNextOfficeId($request);

        $this->workflow->signAndForward(
            submission: $submission,
            stageSlug: $stage->slug,
            signer: Auth::user(),
            data: $data['fields'],
            signatureContext: $this->signatureContext($request),
            nextAssigneeId: $nextAssigneeId,
            attachments: $request->file('attachments', []),
            leadershipCategory: $leadershipCategory,
            nextOfficeId: $nextOfficeId,
        );

        return redirect()->route('admin.forms.show', $submission->id)
            ->with('success', 'Form signed and forwarded.');
    }

    public function reject(Request $request, FormSubmission $submission)
    {
        $this->authorize('reject', $submission);

        $request->validate(['reason' => 'required|string|max:2000']);

        $this->workflow->reject($submission, Auth::user(), $request->input('reason'));

        return redirect()->route('admin.forms.show', $submission->id)
            ->with('success', 'Form sent back to the requisitioner.');
    }

    /**
     * Reroute the current stage to another active member of the same office.
     * Used when the current assignee is on leave / unavailable and the office
     * head needs to keep the form moving.
     */
    public function reassign(Request $request, FormSubmission $submission)
    {
        $this->authorize('reassign', $submission);

        $data = $request->validate([
            'new_assignee_id' => 'required|integer|exists:users,id',
            'reason'          => 'required|string|max:2000',
        ]);

        $newAssignee = User::find($data['new_assignee_id']);
        abort_unless($newAssignee, 404);

        $this->workflow->reassign(
            $submission,
            $newAssignee,
            Auth::user(),
            $data['reason'],
        );

        return redirect()->route('admin.forms.show', $submission->id)
            ->with('success', "Form reassigned to {$newAssignee->first_name} {$newAssignee->last_name}.");
    }

    public function cancel(FormSubmission $submission)
    {
        $this->authorize('cancel', $submission);
        $this->workflow->cancel($submission, Auth::user());

        return redirect()->route('admin.forms.portal')
            ->with('success', 'Form cancelled.');
    }

    public function addComment(Request $request, FormSubmission $submission)
    {
        $this->authorize('comment', $submission);

        $data = $request->validate([
            'message'     => 'required|string|max:5000',
            'is_internal' => 'nullable|boolean',
        ]);

        $isInternal = !empty($data['is_internal']);
        if ($isInternal && (int) $submission->created_by === (int) Auth::id()) {
            $isInternal = false;
        }

        $this->workflow->addComment($submission, Auth::user(), $data['message'], $isInternal);

        return back()->with('success', 'Comment added.');
    }

    // ===========================================================
    // DOWNLOADS
    // ===========================================================

    public function downloadAttachment(Request $request, FormSubmission $submission, FormAttachment $attachment)
    {
        abort_unless($attachment->form_submission_id === $submission->id, 404);
        $this->authorize('downloadAttachment', $submission);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($attachment->path), 404);

        // ?inline=1 returns the file with Content-Disposition: inline so the
        // attachment-viewer-modal can render it (PDFs in <iframe>, images in <img>).
        // Without it we fall back to a regular forced download.
        if ($request->boolean('inline')) {
            return response()->file($disk->path($attachment->path), [
                'Content-Type'        => $attachment->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . str_replace('"', '', (string) $attachment->name) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return $disk->download($attachment->path, $attachment->name);
    }

    public function downloadPdf(FormSubmission $submission)
    {
        $this->authorize('downloadPdf', $submission);

        $definition = $submission->definition();
        abort_unless($definition, 500);

        $submission->load(['signatures.user', 'creator', 'comments.user', 'attachments']);

        $pdf = Pdf::loadView($definition->pdfView(), [
            'submission' => $submission,
            'definition' => $definition,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = strtoupper($submission->form_code) . '-' . $submission->reference . '.pdf';

        return $pdf->stream($filename);
    }

    // ===========================================================
    // AJAX: office members (recipient picker, restricted to the
    // specific office that this stage is allowed to forward to)
    // ===========================================================

    public function officeMembers(Request $request, string $officeSlug)
    {
        $office = Office::where('slug', $officeSlug)->first();
        abort_unless($office, 404);

        $members = $office->activeUsers()
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'office_user.is_head')
            ->orderByDesc('office_user.is_head')
            ->orderBy('users.first_name')
            ->get()
            ->map(fn ($u) => [
                'id'      => $u->id,
                'name'    => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')),
                'email'   => $u->email,
                'is_head' => (bool) $u->is_head,
            ]);

        return response()->json([
            'office' => [
                'slug' => $office->slug,
                'name' => $office->name,
            ],
            'members' => $members,
        ]);
    }

    // ===========================================================
    // INPUT HELPERS
    // ===========================================================

    /**
     * Validate the incoming stage input against the stage's FormField rules.
     * Extracts ONLY the declared field names so HTML tampering cannot inject
     * extra fields into section_data.
     */
    protected function validateStageInput($request, $definition, $stage, bool $requireSignature): array
    {
        // Pre-clean TYPE_TABLE inputs by dropping rows where every cell is blank.
        // Done BEFORE validation so a user adding an extra empty row doesn't
        // trip required-column rules, and so we never persist phantom rows.
        $cleaned = $request->all();
        foreach ($stage->fields as $field) {
            if ($field->type !== FormField::TYPE_TABLE) {
                continue;
            }
            $rows = $cleaned[$field->name] ?? null;
            if (!is_array($rows)) {
                continue;
            }
            $rows = array_values(array_filter($rows, function ($row) {
                if (!is_array($row)) return false;
                foreach ($row as $cell) {
                    if (trim((string) $cell) !== '') return true;
                }
                return false;
            }));
            $cleaned[$field->name] = $rows;
            $request->merge([$field->name => $rows]);
        }

        $rules = $stage->validationRules();

        $validator = Validator::make($cleaned, $rules);
        $validator->validate();

        $values = [];
        foreach ($stage->fieldNames() as $name) {
            $raw = $cleaned[$name] ?? null;
            $values[$name] = $raw;
        }

        return ['fields' => $values];
    }

    /**
     * Determine + validate which next-stage assignee the request asked for.
     * Returns null when no downstream stage exists (form completion).
     *
     * For leadership-pool next stages we additionally require a category
     * (hod / dean / director) and the workflow service will validate that
     * the picked user truly carries a position tagged with that category.
     */
    protected function validatedNextAssignee($request, $definition, $stage, ?array $thisStageData = null): ?int
    {
        $vcField = $definition->vcReferralFieldName();
        $referToVc = $thisStageData && $vcField && !empty($thisStageData[$vcField]);

        if ($referToVc && in_array('vc', $stage->branches, true)) {
            $nextStage = $definition->stage('vc');
        } else {
            $nextStage = $definition->nextStageAfter($stage->slug, includeOptional: false);
        }

        if (!$nextStage) {
            return null;
        }

        // POOL_CREATOR auto-routes to the form's original applicant — no
        // assignee_id is expected from the client. The workflow service
        // resolves the user from submission->created_by.
        if ($nextStage->isCreatorPool()) {
            return null;
        }

        $assigneeId = $request->input('next_assignee_id');
        if (!$assigneeId) {
            return null;
        }

        return (int) $assigneeId;
    }

    /**
     * Optional leadership category (hod/dean/director) the requisitioner
     * picked when the next stage is a leadership pool. For the
     * leadership-or-office pool the additional synthetic value 'office' is
     * also accepted and signals to the workflow service that the form is
     * being routed to the head of a specific Office (id passed separately).
     * Returned as null when not applicable.
     */
    protected function extractLeadershipCategory(Request $request): ?string
    {
        $value = $request->input('next_leadership_category');
        if (!$value) {
            return null;
        }
        $allowed = array_merge(array_keys(Position::CATEGORIES), ['office']);
        return in_array($value, $allowed, true) ? $value : null;
    }

    /**
     * Office id chosen on a leadership-or-office pool stage when the user
     * picked the "Office" chip. The workflow service will resolve to that
     * office's head as the next assignee.
     */
    protected function extractNextOfficeId(Request $request): ?int
    {
        $value = $request->input('next_office_id');
        return $value ? (int) $value : null;
    }

    /**
     * Pre-populate the requisitioner section with the user's profile data
     * (still editable, but saves typing).
     */
    protected function prefillRequisitioner($user, $stage): array
    {
        $positionName   = optional($user->position)->name;
        $departmentName = optional($user->department)->name;
        $fullName       = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        // Map every known field-name a form might use to its user-profile
        // value. The loop below only copies values that actually appear on
        // the current stage, so forms that don't have a given field are
        // unaffected. Add new keys here whenever a form introduces a field
        // that's derivable from the user's profile.
        $defaults = [
            // Identity
            'name'                   => $fullName,
            'surname'                => $user->last_name,    // academic renewal
            'first_names'            => $user->first_name,   // academic renewal
            'email'                  => $user->email,

            // Department / faculty / unit — known synonyms used across forms.
            'faculty_department'        => $departmentName,   // leave forms
            'department_section_unit'   => $departmentName,   // vehicle maintenance
            'faculty_centre_dept'       => $departmentName,   // renewal of appointment (senior & junior staff)
            'faculty_school_department' => $departmentName,   // renewal of appointment (academic)

            // Position / rank / job title — known synonyms.
            'job_title'              => $positionName,      // PR / PWA
            'rank'                   => $positionName,      // leave forms
            'post_status'            => $positionName,      // vehicle maintenance
            'present_position_rank'  => $positionName,      // renewal of appointment (senior & junior staff)
            'current_position_rank'  => $positionName,      // renewal of appointment (academic)

            // Contact (not stored on User today — left null intentionally).
            'phone'                  => null,
        ];

        $prefill = [];
        foreach ($stage->fieldNames() as $name) {
            if (array_key_exists($name, $defaults) && $defaults[$name]) {
                $prefill[$name] = $defaults[$name];
            }
        }

        return $prefill;
    }

    protected function signatureContext(Request $request): array
    {
        return [
            'signature_data'       => $request->input('signature_data'),
            'reuse_saved'          => (bool) $request->input('reuse_saved_signature'),
            'save_as_my_signature' => (bool) $request->input('save_as_my_signature'),
            'ip'                   => $request->ip(),
            'user_agent'           => substr((string) $request->userAgent(), 0, 2000),
        ];
    }
}
