<?php

namespace App\Forms\Definitions;

/**
 * Casual Leave Application Form (Form CL) — Catholic University of Ghana,
 * Fiapre-Sunyani. Structurally identical to the Annual Leave form on paper;
 * all the shared mechanics live on {@see BaseLeaveApplicationForm}.
 *
 * Workflow recap:
 *   1. officer       — Officer fills the application and signs
 *   2. recommender   — Dean / HOD / Director OR Head of any Office signs
 *   3. registrar     — Registrar approves and signs
 */
class CasualLeaveApplicationForm extends BaseLeaveApplicationForm
{
    public function slug(): string
    {
        return 'casual-leave-application';
    }

    public function code(): string
    {
        return 'CL';
    }

    public function title(): string
    {
        return 'Casual Leave Application Form';
    }

    public function description(): string
    {
        return 'Apply for casual leave (short-notice or emergency time off). Routes to a Dean / HOD / Director or any Office head for recommendation, then on to the Registrar for approval.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.casual-leave-application';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.casual-leave-application';
    }
}
