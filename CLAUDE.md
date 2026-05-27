# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Dependencies
composer install
npm install

# Development
php artisan serve          # Start local dev server
npm run dev                # Vite asset hot-reload

# Build
npm run build              # Compile production assets
php artisan config:cache   # Cache config for production

# Database
php artisan migrate
php artisan db:seed

# Testing
php artisan test           # Run all tests
php artisan test --filter=ExampleTest   # Run single test

# Code style
vendor/bin/pint            # Auto-fix PHP style (Laravel standards)

# Key artisan utilities
php artisan tinker
php artisan queue:work     # Process SendCampaignEmail jobs
```

## Critical: Reversed Role Terminology

The database role values are **opposite** to their UI display labels — see [ROLE_TERMINOLOGY.md](ROLE_TERMINOLOGY.md):

| Database value | Displayed as in UI |
|---|---|
| `'user'` | "Admin" |
| `'admin'` | "User" |
| `'super_admin'` | "Super Admin" |

Always use database values in backend queries/middleware; only swap labels in Blade views. This applies everywhere: role checks, permission gates, display components.

## Architecture

**Laravel 11** full-stack app (PHP 8.2+, Blade, Vite/Axios). SQLite by default; supports MySQL/PostgreSQL via `.env`.

### Route & Controller Structure

All routes are in [routes/web.php](routes/web.php) (300+ routes). Controllers are split by access tier:

- [app/Http/Controllers/Frontend/](app/Http/Controllers/Frontend/) — Public-facing pages (landing, pricing, etc.)
- [app/Http/Controllers/Dashboard/](app/Http/Controllers/Dashboard/) — Authenticated user features
- [app/Http/Controllers/SuperAdmin/](app/Http/Controllers/SuperAdmin/) — System administration

### Middleware Stack

Request flow for authenticated routes: `Auth` → `SubscriptionActiveMiddleware` → `CheckMaintenanceMode`. Users without an active subscription are blocked at the middleware level before reaching any controller.

### Key Models

- `User` — roles: `super_admin`, `admin` (UI: "User"), `user` (UI: "Admin"). Belongs to many `Office` rows via `office_user` (pivot has `is_head`, `is_active`); `activeOffices()` is what form routing consults.
- `Exam`, `File`, `Folder` — document archive and organization
- `EmailCampaign`, `EmailCampaignRecipient` — bulk email campaigns (async via `SendCampaignEmail` job)
- `SystemSubscription`, `PaymentTransaction` — licensing and Paystack payment records
- `Committee`, `Department`, `Position` — organizational structure. `Position::CATEGORIES` (`hod`, `dean`, `director`) drives leadership-pool routing in the Forms system.
- `Office` — institutional offices (Finance, Internal Audit, Registrar, VC, Procurement Committee, Director of Finance) that Forms route through. Separate from `Department` because routing must be deterministic.
- `FormSubmission`, `FormSignature`, `FormAttachment`, `FormComment`, `UserSignature` — Forms workflow system (see below).
- `MemoReply` — chat-style memo system (UIMMS)
- `Notification` — in-app notifications written alongside many actions (memo replies, form assignments, etc.)

### Services

Located in [app/Services/](app/Services/):
- `PaystackService` — payment processing and webhook handling
- `ResendMailService` — primary email delivery (SMTP: smtp.resend.com)
- Mailjet is configured as a fallback mail provider
- `Services/Forms/FormWorkflowService` — single mutator for all form state transitions (see Forms section)
- `Services/Signing/SignatureService` + `InAppSignatureProvider` — pluggable e-signature backend; bind a different `SignatureProvider` in `AppServiceProvider` to swap in DocuSign / PandaDoc / SignNow without controller changes.

### Email / Queue

Bulk campaign emails are sent asynchronously via the `SendCampaignEmail` job — requires `php artisan queue:work` to be running. Email templates live in [resources/views/mails/](resources/views/mails/). Form notification emails (`FormStageAssigned`, `FormSubmissionRejected`, `FormSubmissionCompleted`) are sent synchronously inside the workflow service but wrapped in try/catch so SMTP failures never abort the workflow.

### Frontend Assets

Vite config at [vite.config.js](vite.config.js). Blade views are organized under [resources/views/](resources/views/) by tier: `frontend/`, `admin/`, `super-admin/`, `subscription/`, and shared `components/`.

### PDF Generation

Uses `barryvdh/laravel-dompdf` for invoice/document export and for the Forms PDF pipeline (each form declares its own `pdfView()`; the shared layout at [resources/views/admin/forms/pdf/_layout.blade.php](resources/views/admin/forms/pdf/_layout.blade.php) renders any form definition).

## Forms Workflow System

A form-agnostic workflow + e-signing engine at `admin/forms/*` ([routes/web.php](routes/web.php) under the `admin.forms.*` name prefix). Two forms ship today — Payment Requisition (`PR`) and Purchase/Works Authorization (`PWA`) — both registered in `AppServiceProvider::register()`.

### Adding a new form
1. Create a class in [app/Forms/Definitions/](app/Forms/Definitions/) extending `BaseFormDefinition`. Declare `slug`, `code`, `title`, `description`, `stages()`, `templateView()`, `pdfView()`, and optionally `amountFieldName()` / `vcReferralFieldName()`.
2. Register it: `$registry->register(new YourForm())` in [AppServiceProvider::register()](app/Providers/AppServiceProvider.php).

No new routes, controllers, migrations, or DB columns are needed. The gallery, portal, compose/show pages, signing, PDF, attachments, comments, audit trail, notifications, and policies all pick it up automatically.

### Stage routing
A `FormStage` resolves recipients from one of two pools:
- `POOL_OFFICE` — pick an active member of the named `Office` (slug). The form will refuse to forward if the office has no active members or the picked user is not a member of that office.
- `POOL_LEADERSHIP` — for HOD / Dean / Director stages. The user picks a category, then a person whose `Position.category` matches. There is no "single head" because every department/faculty has its own.

Stages can declare `branches: ['vc']` + a corresponding `vcReferralFieldName` checkbox — when ticked, the workflow service routes to the optional `vc` stage instead of the natural next stage.

### Single mutator + tamper-evident chain
**All state transitions go through [FormWorkflowService](app/Services/Forms/FormWorkflowService.php) inside a DB transaction.** Controllers never mutate `FormSubmission` directly — this is a security boundary. Each signature stores `payload_hash = sha256(canonical_json(section_data))` and `chain_hash = sha256(prior_hash || payload_hash || user_id || signed_at_iso)`, so tampering with any prior signed stage breaks the chain on subsequent stages.

### Authorization
[FormSubmissionPolicy](app/Policies/FormSubmissionPolicy.php) is the single source of truth. Visibility = creator OR current assignee OR has signed any stage OR active member of the current office. Edit/sign requires `status == in_progress` AND user == `current_assignee_id`. Super admin auto-passes via the `before` hook. Office CRUD ([app/Http/Controllers/Dashboard/OfficeController.php](app/Http/Controllers/Dashboard/OfficeController.php)) is gated by the `institutional_admin` middleware.

### Sidebar badge
The "Forms Portal" sidebar badge (`$awaitingFormsCount`) is computed globally in the `View::composer('*')` block in [AppServiceProvider::boot()](app/Providers/AppServiceProvider.php) — alongside other sidebar counters like `unreadMemosCount`. Add new global view-data there, not in individual controllers.
