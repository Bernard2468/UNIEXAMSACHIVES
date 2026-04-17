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

- `User` — roles: `super_admin`, `admin` (UI: "User"), `user` (UI: "Admin")
- `Exam`, `File`, `Folder` — document archive and organization
- `EmailCampaign`, `EmailCampaignRecipient` — bulk email campaigns (async via `SendCampaignEmail` job)
- `SystemSubscription`, `PaymentTransaction` — licensing and Paystack payment records
- `Committee`, `Department`, `Position` — organizational structure
- `MemoReply` — chat-style memo system (UIMMS)

### Services

Located in [app/Services/](app/Services/):
- `PaystackService` — payment processing and webhook handling
- `ResendMailService` — primary email delivery (SMTP: smtp.resend.com)
- Mailjet is configured as a fallback mail provider

### Email / Queue

Bulk campaign emails are sent asynchronously via the `SendCampaignEmail` job — requires `php artisan queue:work` to be running. Email templates live in [resources/views/mails/](resources/views/mails/).

### Frontend Assets

Vite config at [vite.config.js](vite.config.js). Blade views are organized under [resources/views/](resources/views/) by tier: `frontend/`, `admin/`, `super-admin/`, `subscription/`, and shared `components/`.

### PDF Generation

Uses `barryvdh/laravel-dompdf` for invoice and document PDF export.
