# Hospital Management System

A full-featured Hospital Management System (HMS) built with **Laravel 13**, **Tailwind CSS**, and **Alpine.js**. Designed for clinics and hospitals to manage patients, OPD/IPD workflows, pharmacy, laboratory, staff, appointments, and financials — all from a single, modern web interface with dark/light mode support.

---

## Features

### Clinical
- **Patient Registry** — MR number generation, CNIC, blood group, age, medical history, Excel export
- **OPD (Outpatient)** — Visits, prescriptions, printable invoices
- **IPD (Inpatient)** — Admissions, ward/bed assignment, treatments, discharge, invoices
- **Appointments** — Scheduling, status tracking (scheduled → confirmed → completed)
- **Tokens** — Queue token generation per shift (morning / evening / night), per doctor/department
- **Laboratory** — Test bookings, result entry, report verification, printable lab reports

### Pharmacy
- **Medicine Catalogue** — Categories, dosage forms, suppliers, stock levels, low-stock alerts
- **POS Sales** — Cart-based point-of-sale with prescription requirement enforcement
- **Purchases** — Supplier invoices, batch tracking, expiry dates, stock auto-increment
- **Suppliers** — Supplier directory with purchase history

### Administration
- **Doctors** — Profiles, specializations, department linkage
- **Staff** — Employee management, roles, salary structures
- **Departments** — Organisational units linked to doctors and OPD
- **Wards & Beds** — Ward types (general / ICU / private), bed availability tracking
- **Shifts** — Shift definitions and assignments

### Finance
- **Expenses** — Categorised expense tracking with approval workflow
- **Salaries** — Salary structures and payment recording
- **Reports** — OPD, laboratory, and pharmacy reports with date filters; Excel/PDF export
- **Daily & Monthly Closing** — Shift closing summaries

### Platform
- **Role-based access control** — 8 roles (super_admin, hospital_admin, receptionist, doctor, nurse, pharmacist, lab_technician, accountant) with 60+ granular permissions via Spatie Laravel Permission
- **Audit trail** — Full change history via Laravel Auditing
- **REST API** — Sanctum-authenticated endpoints for patients, OPD, IPD, appointments, lab, doctors, dashboard, and push notifications
- **Push notifications** — In-app notification bell with unread count
- **PDF generation** — Invoices and reports via DomPDF
- **Excel export** — Patient lists, purchases, and reports via Maatwebsite Excel
- **Dark / Light mode** — Persisted via `localStorage`, defaults to dark
- **Hospital Settings** — Logo, name, address, timezone, date format

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Frontend | Blade, Tailwind CSS (CDN), Alpine.js |
| Auth | Laravel Breeze + Sanctum (API) |
| Permissions | Spatie Laravel Permission v8 |
| Database | MySQL (SQLite for local dev) |
| PDF | barryvdh/laravel-dompdf |
| Excel | Maatwebsite/Excel |
| Auditing | owen-it/laravel-auditing |

---

## Requirements

- PHP >= 8.3
- Composer
- MySQL 8+ (or SQLite for local development)
- Node.js (optional — no build step required, Tailwind is loaded via CDN)

---

## Installation

```bash
# 1. Clone the repository
git clone <repository-url> hospital-management-system
cd hospital-management-system

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure your database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hms
DB_USERNAME=root
DB_PASSWORD=

# 6. Run migrations and seed default data
php artisan migrate --seed

# 7. Create the storage symlink
php artisan storage:link

# 8. Start the development server
php artisan serve
```

Access the app at `http://localhost:8000`

---

## Default Credentials

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@hms.com | Admin@123 |
| Receptionist | receptionist@hms.com | Admin@123 |

---

## Roles & Permissions

| Permission Area | super_admin | hospital_admin | receptionist | doctor | nurse | pharmacist | lab_technician | accountant |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Patients | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | |
| OPD | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | |
| IPD | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | |
| Appointments | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | | |
| Tokens | ✓ | ✓ | ✓ | ✓ | ✓ | | | |
| Pharmacy / Sales | ✓ | ✓ | | | | ✓ | | |
| Purchases | ✓ | ✓ | | | | ✓ | | |
| Laboratory | ✓ | ✓ | ✓ | | ✓ | ✓ | ✓ | |
| Doctors / Staff | ✓ | ✓ | | | | | | |
| Expenses | ✓ | ✓ | | | | | | ✓ |
| Salaries | ✓ | ✓ | | | | | | ✓ |
| Reports | ✓ | ✓ | | | | | | ✓ |
| Settings | ✓ | ✓ | | | | | | |

---

## REST API

Base URL: `/api`

Authentication: Bearer token via `POST /api/auth/login`

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/auth/login` | Obtain API token |
| POST | `/api/auth/logout` | Revoke token |
| GET | `/api/auth/me` | Authenticated user info |
| GET/POST | `/api/patients` | List / create patients |
| GET/PUT/DELETE | `/api/patients/{id}` | Show / update / delete patient |
| GET/POST | `/api/opd` | OPD visits |
| GET/POST | `/api/ipd` | IPD admissions |
| GET/POST | `/api/appointments` | Appointments |
| GET/POST | `/api/lab` | Lab bookings |
| GET | `/api/doctors` | Doctor list |
| GET | `/api/dashboard` | Dashboard stats |
| GET | `/api/notifications` | In-app notifications |
| GET | `/api/notifications/unread-count` | Unread notification count |
| POST | `/api/notifications/mark-all-read` | Mark all read |

---

## Project Structure

```
app/
├── Http/Controllers/       # Web controllers (one per resource)
│   └── Api/                # API controllers (Sanctum-authenticated)
├── Models/                 # Eloquent models (37 models)
├── Services/               # Business logic (PharmacyService, etc.)
├── Notifications/          # Push notifications (LowStockAlert, etc.)
└── Exports/                # Excel export classes

resources/views/
├── layouts/                # hms.blade.php (main), guest.blade.php (auth)
├── components/             # Reusable Blade components (badges, form fields)
├── patients/ appointments/ opd/ ipd/ tokens/
├── pharmacy/ medicines/ purchases/ suppliers/
├── lab/ departments/ doctors/ staff/ wards/
├── expenses/ salaries/ shifts/ reports/ settings/
└── auth/                   # Login, password reset

database/
├── migrations/             # 27 migration files
└── seeders/                # Roles, permissions, departments, admin user
```

---

## Key Design Decisions

- **No build step** — Tailwind CSS is loaded from CDN. No `npm install` or `npm run build` required.
- **CSS utility classes** — Custom `.field`, `.field-label`, `.btn-cancel`, and sidebar classes are defined in the layout `<style>` block for consistent form and UI styling across all pages.
- **Dark mode default** — Controlled via Alpine.js + `localStorage`. Defaults to dark unless the user explicitly switches to light.
- **Blade-only frontend** — No SPA framework. Alpine.js handles interactivity (modals, toggles, dynamic carts).
- **Sanctum for API** — Web session auth for Blade views; token-based Sanctum auth for the REST API.

---

## Running Tests

```bash
php artisan test
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
