# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

A Hospital Management System built with **Laravel 13 / PHP 8.3**. Server-rendered Blade frontend with Alpine.js for interactivity; a parallel Sanctum-authenticated REST API under `/api`. Domain areas: patients, OPD/IPD, appointments, queue tokens, pharmacy (POS + purchases + stock), laboratory, doctors/staff, and finance (expenses, salaries, reports, daily/monthly closing).

## Commands

```bash
# Full dev environment (server + queue + pail logs + vite, run concurrently)
composer dev

# Just the web server
php artisan serve

# Tests (clears config, then runs PHPUnit against in-memory SQLite)
composer test
php artisan test
php artisan test --filter=PatientWebTest            # single class
php artisan test tests/Feature/Api/AuthApiTest.php  # single file

# Lint / format (Laravel Pint)
./vendor/bin/pint            # fix
./vendor/bin/pint --test     # check only

# DB reset + seed
php artisan migrate:fresh --seed

# Frontend assets (optional — see Frontend note)
npm run dev      # vite dev server
npm run build    # production build
```

Tests use an **in-memory SQLite** DB configured in `phpunit.xml`; no DB setup needed. Health check endpoint is `/up`.

## Request lifecycle & layering

The codebase follows a consistent **thin controller → service → model** flow. When adding behavior to an existing domain, put logic in the service, not the controller.

1. **Routes** — `routes/web.php` (all under one `auth` middleware group), `routes/api.php` (under `auth:sanctum`), `routes/auth.php` (Breeze auth scaffolding). There is **no per-route permission middleware**; authorization happens inside controllers.

2. **Controllers** (`app/Http/Controllers/`) — one per resource, constructor-inject their service. Every action starts with `$this->authorize('<permission>')`, delegates to the service, then returns a view or redirects with a `success` flash message. The base `Controller` only adds the `AuthorizesRequests` trait.

3. **Services** (`app/Services/`) — `OpdService`, `IpdService`, `LabService`, `PharmacyService`, `PatientService`, `DashboardService`. They own:
   - `list(array $filters)` methods that build filtered, eager-loaded, paginated queries (`->paginate(20)->withQueryString()`), using `when()` for optional filters and a model `search()` scope via `whereHas`.
   - Record creation with derived values (`net_amount`, totals, profit) and generated identifiers.
   - **Multi-table writes wrapped in `DB::transaction`** (pharmacy sales/purchases, lab bookings, results). Follow this pattern for any operation that touches more than one table.

4. **Models** (`app/Models/`, ~39) — see conventions below.

## Web vs API

- **Web**: session auth, returns Blade views, redirects with flash messages.
- **API** (`app/Http/Controllers/Api/`, `*ApiController`): Sanctum bearer-token auth. `POST /api/auth/login` returns a token; `/api/auth/me` returns the user with `permissions` and `roles`. JSON responses use **API Resources** (`app/Http/Resources/`, e.g. `PatientResource`) for output shaping. Exceptions auto-render as JSON for `api/*` paths (configured in `bootstrap/app.php`).

API controllers often re-implement filtering inline rather than reusing the web service `list()` methods — keep both in sync when changing query logic.

## Authorization

Spatie Laravel Permission. 8 seeded roles (`super_admin`, `hospital_admin`, `receptionist`, `doctor`, `nurse`, `pharmacist`, `lab_technician`, `accountant`) and ~80 permissions. Permission names are `"<verb> <area>"` but **not uniformly CRUD** — e.g. `view opd`, `manage medicines`, `create sales`, `enter lab results`, `verify lab reports`, `manage lab tests`, `approve expenses`, `discharge patients`, `close shifts`.

**`app/Support/Permissions.php` is the single source of truth** for the permission catalogue (grouped by module label). `RolePermissionSeeder` seeds from `Permissions::all()`, and the Access Control UI builds its checkbox matrix from `Permissions::groups()`. Add new permissions there so the seeder and UI stay in sync; check it (or the seeder) for the exact string before calling `authorize()` / `@can`.

Enforcement is in two places only: `$this->authorize('...')` in controllers and `@can('...')` in Blade. `User` also has a `user_type` column (mirrors the role) used by `User::homeRoute()` to redirect each role to its landing page after login.

**Access Control module** (`RoleController`, `PermissionController`, and `UserController::permissions/updatePermissions`): full CRUD for roles (with a grouped permission matrix), create/delete of individual permissions, and per-user **direct** permissions layered on top of role-inherited ones (`syncPermissions` touches only direct grants). The `super_admin` role is protected — it always retains all permissions and cannot be renamed or deleted; roles still assigned to users cannot be deleted; core Access Control permissions cannot be deleted. The shared checkbox UI lives in `resources/views/partials/permission-matrix.blade.php`.

## Model conventions

- **Auditing**: most domain models implement `OwenIt\Auditing\Contracts\Auditable` + use `AuditableTrait` (writes to the `audits` table). Preserve this when adding models.
- **Soft deletes** are widespread. Relations to possibly-deleted users use `withTrashed()` (e.g. `Doctor::user()`) so listings never break on a deactivated account.
- **Generated identifiers** live as static methods on the model, not in services: `Patient::generateMrNumber()`, `OpdVisit::generateVisitNumber()`, `Appointment::generateNumber()`, `IpdAdmission::generateAdmissionNumber()`, `LabBooking::generateNumber()`, `Sale::generateInvoiceNumber()`, `Purchase::generateNumber()`.
- **Scopes**: `active()` and a `search()` scope are common across models.
- `Doctor::available_days` has custom accessor/mutator handling **double-encoded JSON** from legacy seed data — normalizes to a lowercase array. Don't assume it's a plain cast.

## Domain specifics

- **Shifts**: operations bucket into `morning` / `evening` / `night`, derived from the current hour (8–14 morning, 14–20 evening, else night). This logic is duplicated in `OpdService::currentShift()`, `LabService`, and `PharmacyService` — keep them consistent.
- **Pharmacy stock**: stock is tracked **twice** — a denormalized `medicines.stock_quantity` counter (incremented/decremented) *and* a `MedicineStock` ledger row (type `in`/`out`/`adjustment`, with `reference_type`/`reference_id`). Purchases also create `MedicineBatch` rows with expiry and remaining quantity. Any stock change must update both.
- **Lab**: a `LabBooking` has `LabBookingItem`s; one `LabReport` is created per test up front, then filled in by `saveResults`. Prices come authoritatively from the `lab_tests` table — never trusted from the client. Booking auto-completes when all reports are completed.
- **IPD**: admitting/discharging flips the linked `Bed` status (`occupied`/`available`); charges are computed from days × daily bed charge plus itemized charges.

## Notifications

In-app notification bell backed by Laravel's `database` channel; several notifications also send `mail` (see `via()` in `app/Notifications/`). Dispatched via `$user->notify(new ...)` from controllers after create actions (low stock, appointment scheduled, IPD admission, lab result ready, salary generated). API endpoints under `/api/notifications` expose list, unread count, and mark-all-read.

## Exports & PDF

- **Excel**: `app/Exports/` (Maatwebsite Excel) — patient lists, purchases, and each report type.
- **PDF**: `barryvdh/laravel-dompdf` for invoices, slips, lab reports, and closing reports — rendered from dedicated `*-pdf.blade.php` / `print` / `invoice` / `slip` controller actions.

## Frontend

**No build step is required for normal development.** The main layout `resources/views/layouts/hms.blade.php` loads Tailwind, Alpine.js, Chart.js, and Tom Select (searchable dropdowns) from **CDNs**. A Vite/Tailwind toolchain exists in `package.json` but the running app uses the CDN scripts. Shared UI utility classes (`.field`, `.field-label`, `.btn-cancel`, sidebar styles) are defined in the layout's `<style>` block. Dark mode is the default, toggled via Alpine.js + `localStorage`.

Views are one directory per resource under `resources/views/` (e.g. `opd/`, `lab/`, `lab-tests/`). Note: **view path ≠ route name** — lab-test views live in `resources/views/lab-tests/` but their routes are named `lab.tests.*`. Reusable Blade components are in `resources/views/components/`.

## Seeders

`DatabaseSeeder` wires up `RolePermissionSeeder`, `AdminUserSeeder` (default `admin@hms.com` / `Admin@123`), departments, shifts, expense categories, and hospital settings. `DemoDataSeeder` / `BulkDataSeeder` generate sample/volume data for development.

## Deployment

See `DEPLOYMENT.md`. Production uses MySQL; local dev and tests use SQLite.
