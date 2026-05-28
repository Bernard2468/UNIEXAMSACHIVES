<?php

namespace App\Forms\Definitions;

/**
 * Annual Leave Application Form (Form AL) — Catholic University of Ghana,
 * Fiapre-Sunyani. All structure (stages, fields, workflow, footer note)
 * lives on the shared {@see BaseLeaveApplicationForm}; this class only
 * declares the form's identity.
 *
 * Workflow recap:
 *   1. officer       — Officer fills the application and signs
 *   2. recommender   — Dean / HOD / Director OR Head of any Office signs
 *   3. registrar     — Registrar approves and signs
 */
class AnnualLeaveApplicationForm extends BaseLeaveApplicationForm
{
    public function slug(): string
    {
        return 'annual-leave-application';
    }

    public function code(): string
    {
        return 'AL';
    }

    public function title(): string
    {
        return 'Annual Leave Application Form';
    }

    public function description(): string
    {
        return 'Apply for annual leave. Routes to a Dean / HOD / Director or any Office head for recommendation, then on to the Registrar for approval.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.annual-leave-application';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.annual-leave-application';
    }
}
