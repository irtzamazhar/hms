# Multi-Tenant SaaS Conversion — Roadmap

Converting the HMS from single-install to a **shared-codebase, shared-database,
row-isolated** multi-tenant SaaS. One deployment serves all hospitals; each
hospital's data is isolated by a `hospital_id` and a global query scope.

**Business model:** every hospital gets a **12-month free trial**, then must hold
a paid subscription. Access is gated on the `hospitals` row
(`status` + `subscription_status` + `trial_ends_at` / `subscribed_until`).

## Why shared-DB row-level isolation
- **Deploy once** → every tenant gets fixes/patches immediately (the stated goal).
- Far simpler ops than database-per-tenant (one schema, one migration run).
- Isolation is enforced by a global scope on `hospital_id`, set from the
  authenticated user / subdomain on every request.
- (Database-per-tenant remains a future option for enterprise buyers needing
  physical isolation — the `Tenancy` abstraction leaves room for it.)

## Phases (each phase keeps the app working + tests green)

### ✅ Phase 1 — Tenant foundation (DONE)
- `hospitals` table + `Hospital` model (trial/subscription lifecycle: `onTrial`,
  `trialExpired`, `subscriptionActive`, `hasAccess`, `startTrial`, `trialDaysLeft`).
- `config/tenancy.php` (trial length, default tenant id).
- `App\Support\Tenancy` request-scoped tenant context (`set/current/id/runFor`).
- `App\Models\Concerns\BelongsToTenant` trait (auto-fill + global scope) — built,
  **not yet applied** (needs the column first).
- `DefaultHospitalSeeder` (tenant id=1 for existing data).
- Tests: `HospitalTenantTest`.

### Phase 2 — Add `hospital_id` to tenant-owned tables (breaking; do carefully)
- Migration adding nullable `hospital_id` (FK → hospitals) to every tenant table
  (patients, opd_visits, ipd_admissions, appointments, tokens, lab_*, sales,
  purchases, medicines, expenses, salary_*, doctors, staff, departments, wards,
  beds, shifts, audits, users, …).
- Backfill all existing rows to `config('tenancy.default_tenant_id')`, then make
  the column non-nullable + indexed.
- Apply `BelongsToTenant` to those models.

### Phase 3 — Tenant resolution + subscription gating
- `ResolveTenant` middleware: determine the hospital from the authenticated
  user's `hospital_id` (and/or subdomain) and `Tenancy::set()`.
- `EnsureSubscriptionActive` middleware: block tenants whose `hasAccess()` is
  false (trial expired & unsubscribed, or suspended) → redirect to a billing
  notice; keep billing/login routes reachable.
- Trial countdown banner in the layout.

### Phase 4 — Tenant-scoped settings, storage & branding
- Move `HospitalSetting` to per-tenant; namespace uploaded files by tenant.
- Per-tenant logo/theme.

### Phase 5 — Onboarding, billing, hardening
- Self-serve hospital signup (creates tenant via `Hospital::startTrial` + first
  admin user).
- Subscription/billing integration; super-admin tenant management console.
- Cross-tenant isolation tests (a user of hospital A can never read hospital B).
