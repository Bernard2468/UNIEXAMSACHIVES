<?php

namespace App\Http\Controllers\Dashboard;

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

        $submission = $this->workflow->createSubmission(
            definition: $definition,
            creator: Auth::user(),
            requisitionerData: $data['fields'],
            attachments: $request->file('attachments', []),
            action: $action,
            nextAssigneeId: $nextAssigneeId,
            signatureContext: $this->signatureContext($request),
            leadershipCategory: $leadershipCategory,
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

        $nextContext = ['office' => null, 'leadership' => null];
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
            'vcOffice'             => $vcOffice,
            'canFill'              => $canFill,
            'canComment'           => app(\App\Policies\FormSubmissionPolicy::class)->comment($user, $submission),
            'canCancel'            => app(\App\Policies\FormSubmissionPolicy::class)->cancel($user, $submission),
            'canViewInternal'      => $canViewInternal,
            'comments'             => $comments,
            'savedSignature'       => $user->savedSignature,
        ]);
    }

    /**
     * Build the data the recipient picker needs for the given next stage:
     *  - office candidates when the stage routes to an Office
     *  - or a category-keyed map of User collections for leadership pools
     */
    protected function buildNextStageContext(?FormStage $nextStage): array
    {
        $office     = null;
        $leadership = null;

        if (!$nextStage) {
            return ['office' => null, 'leadership' => null];
        }

        if ($nextStage->isLeadershipPool()) {
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
        } elseif ($nextStage->officeSlug) {
            $office = Office::with(['users' => fn ($q) => $q->wherePivot('is_active', true)])
                ->where('slug', $nextStage->officeSlug)
                ->first();
        }

        return [
            'office'     => $office,
            'leadership' => $leadership,
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

        $this->workflow->signAndForward(
            submission: $submission,
            stageSlug: $stage->slug,
            signer: Auth::user(),
            data: $data['fields'],
            signatureContext: $this->signatureContext($request),
            nextAssigneeId: $nextAssigneeId,
            attachments: $request->file('attachments', []),
            leadershipCategory: $leadershipCategory,
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

    public function downloadAttachment(FormSubmission $submission, FormAttachment $attachment)
    {
        abort_unless($attachment->form_submission_id === $submission->id, 404);
        $this->authorize('downloadAttachment', $submission);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($attachment->path), 404);

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
        $rules = $stage->validationRules();

        $validator = Validator::make($request->all(), $rules);
        $validator->validate();

        $values = [];
        foreach ($stage->fieldNames() as $name) {
            $raw = $request->input($name);
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

        $assigneeId = $request->input('next_assignee_id');
        if (!$assigneeId) {
            return null;
        }

        return (int) $assigneeId;
    }

    /**
     * Optional leadership category (hod/dean/director) the requisitioner
     * picked when the next stage is a leadership pool. Returned as null
     * when not applicable.
     */
    protected function extractLeadershipCategory(Request $request): ?string
    {
        $value = $request->input('next_leadership_category');
        if (!$value) {
            return null;
        }
        return in_array($value, array_keys(Position::CATEGORIES), true) ? $value : null;
    }

    /**
     * Pre-populate the requisitioner section with the user's profile data
     * (still editable, but saves typing).
     */
    protected function prefillRequisitioner($user, $stage): array
    {
        $defaults = [
            'name'                => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
            'faculty_department'  => optional($user->department)->name,
            'job_title'           => optional($user->position)->name,
            'phone'               => null,
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
            'signature_data' => $request->input('signature_data'),
            'reuse_saved'    => (bool) $request->input('reuse_saved_signature'),
            'ip'             => $request->ip(),
            'user_agent'     => substr((string) $request->userAgent(), 0, 2000),
        ];
    }
}
