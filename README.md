# Agency Super — Recruitment Agency Management System

A multi-tenant recruitment agency management platform built on **Laravel 13** (PHP 8.3+) with SQLite/MySQL, Redis, and Tailwind CSS (DaisyUI). Designed for Philippine overseas employment agencies to manage applicants, employers, job positions, billing, commissions, marketing partners, and operational workflows from a single dashboard.

**Live site:** [agency.classapparelph.com](https://agency.classapparelph.com)  
**Stack:** Laravel 13 · PHP 8.3 · MySQL · Redis · Tailwind CSS 4 · DaisyUI 5 · Vite

---

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Environment Configuration](#environment-configuration)
- [Database Schema](#database-schema)
- [Application Structure](#application-structure)
- [API & Routes](#api--routes)
- [Portals](#portals)
- [Testing](#testing)
- [Deployment](#deployment)
- [Queue & Background Jobs](#queue--background-jobs)
- [AI Assistant](#ai-assistant)
- [Multi-Tenancy](#multi-tenancy)
- [Contributing](#contributing)

---

## Architecture Overview

Agency Super follows a **multi-tenant** architecture where each agency operates under its own subdomain with isolated data scoped by `agency_id`. The system serves three distinct user portals:

1. **Internal Agency Dashboard** — Agency staff and admins manage the full recruitment lifecycle
2. **Applicant Portal** — Job seekers register, browse jobs, upload documents, track applications
3. **Employer Portal** — Employer partners log in to view billing, SOAs, and applicant details

The app uses Redis for sessions, caching, and the queue driver. Database is MySQL on production (`agency_super_db`) and SQLite in-memory for testing.

---

## Features

### Core Modules

| Module | Description |
|--------|-------------|
| **Agency Management** | Create, activate/suspend agencies with subdomain branding (logo, colors, favicon) |
| **User Management** | Role-based access (super_admin, admin, recruiter, staff, processor, billing, etc.) with granular permissions |
| **Applicant Management** | Full CRUD with sub-tables: education, work experience, skills, certificates, passports, references, salary records, requirements, documents |
| **Employer Management** | Company profiles, job postings, billing, SOA generation |
| **Job Positions** | Position catalog linked to employers with status tracking |
| **Billing & Accounting** | Bills, payments, official receipts, commissions, commission payouts; per-employer/worker/marketing-party accounting views |
| **Marketing Partners** | Marketing agencies and agents with commission tracking |
| **Custom Fields** | Extend applicant/employer records with user-defined fields |
| **Case Management** | Internal case notes and issue tracking API |
| **Reports & Exports** | PDF reports (bills, ORs, commissions, resumes, applicant lists, statistics) + CSV export of applicant data |
| **Notifications** | In-app notification center with unread counts and mark-as-read |
| **Activity Logging** | Audit trail for sensitive operations |
| **AI Assistant** | Natural-language query interface → SQL → results with CSV export and pre-built analytics templates |
| **Document Upload** | Applicant portal document submission with download |
| **Password Reset** | Email-based password reset flow |

### Authentication

- **Internal users:** Email/password with role-based middleware
- **Applicants:** Email/password registration *or* OTP-based login (no password needed)
- **Employers:** Separate login portal scoped to employer data
- **Rate limiting:** Login attempts throttled; AI query endpoint separately rate-limited

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 13.x |
| **PHP** | 8.3+ |
| **Database** | MySQL 8 (production), SQLite (testing) |
| **Cache & Sessions** | Redis |
| **Queue** | Redis (database driver fallback) |
| **Frontend** | Tailwind CSS 4 + DaisyUI 5 |
| **Build Tool** | Vite 8 + laravel-vite-plugin |
| **PDF** | barryvdh/laravel-dompdf |
| **PDF Engine** | DomPDF |
| **Testing** | PHPUnit 12 |
| **Dev Tools** | Laravel Pail (logs), Laravel PAO (profiling), Laravel Pint (linting) |

---

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer 2
- Node.js 20+
- Redis (for sessions, cache, queue)
- MySQL 8 (or SQLite for local dev)

### Installation

```bash
# Clone the repository
cd /var/www/agency.classapparelph.com

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Edit .env with your database credentials (see Environment Configuration below)

# Run migrations and seeders
php artisan migrate
php artisan db:seed

# Build frontend
npm run build

# Start development server
php artisan serve
```

### Seeders

The `DatabaseSeeder` runs these seeders in order:

1. **ReferenceDataSeeder** — Countries (13), Positions (42), Nationalities (11), Religions (7), Civil Statuses (6)
2. **StatusCodesSeeder** — Applicant/employer/position status codes
3. **StatusTransitionSeeder** — Valid status transitions for workflow enforcement
4. **InitialAgencySeeder** — Creates the default/seed agency

---

## Environment Configuration

Key `.env` variables:

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_URL` | `https://agency.classapparelph.com` | App base URL |
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_DATABASE` | `agency_super_db` | MySQL database name |
| `SESSION_DRIVER` | `redis` | Session driver |
| `CACHE_STORE` | `redis` | Cache backend |
| `QUEUE_CONNECTION` | `redis` | Queue driver |

See `.env.example` for the full list.

### Testing Environment

The `phpunit.xml` configuration overrides all env vars for testing:
- `DB_CONNECTION=sqlite` with `:memory:` database
- `APP_ENV=testing`
- `APP_DEBUG=false`
- Rate limiting, Pulse, Telescope all disabled

---

## Database Schema

### Core Tables

| Table | Purpose |
|-------|---------|
| `agencies` | Multi-tenant agency records (name, subdomain, logo, settings, status) |
| `users` | Internal agency staff (linked to agencies) |
| `employers` | Employer/client companies |
| `job_positions` | Job openings linked to employers |
| `applicants` | Worker/applicant profiles |
| `countries` | Reference country list |
| `positions` | Job position types reference |
| `nationalities` | Reference nationalities |
| `religions` | Reference religions |
| `civil_statuses` | Reference civil statuses |
| `status_codes` | Workflow status definitions |
| `status_transitions` | Valid status transitions |
| `bills` | Billing records |
| `payments` | Payment transactions |
| `official_receipts` | Official receipts |
| `commissions` | Commission records |
| `commission_payments` | Commission payout transactions |
| `custom_field_definitions` | Dynamic field definitions |
| `custom_field_values` | Dynamic field values |
| `marketing_agencies` | Marketing partner agencies |
| `marketing_agents` | Individual marketing agents |
| `cases` | Case management records |
| `notifications` | In-app notifications |
| `activity_logs` | Audit trail entries |
| `user_permissions` | Granular user permissions |

### Applicant Sub-Tables

Each applicant can have multiple records in these tables:

| Table | Content |
|-------|---------|
| `applicant_education` | Educational background |
| `applicant_work_experiences` | Work history |
| `applicant_skills` | Skills |
| `applicant_certificates` | Professional certifications |
| `applicant_passports` | Passport details |
| `applicant_references` | Character references |
| `applicant_salary_records` | Salary history |
| `applicant_requirements` | Document requirements checklist |
| `applicant_documents` | Uploaded document files |

### Infrastructure Tables

| Table | Purpose |
|-------|---------|
| `sessions` | Database session storage (fallback for Redis) |
| `cache` | Database cache store (fallback for Redis) |
| `jobs` | Queue jobs table |
| `job_batches` | Queue batch tracking |
| `failed_jobs` | Failed queue job records |

---

## Application Structure

```
app/
├── Console/
│   └── Commands/
│       ├── CheckQueueHealth.php    # Queue health monitor
│       └── DatabaseBackup.php       # Automated DB backup
├── Events/
│   ├── AgencyApproved.php
│   ├── AgencyRejected.php
│   ├── ApplicantStatusChanged.php
│   ├── BillCreated.php
│   ├── DocumentApproved.php
│   ├── DocumentRejected.php
│   └── PaymentReceived.php
├── Http/
│   ├── Controllers/
│   │   ├── AccountingController.php
│   │   ├── AgencyBrandingController.php
│   │   ├── AgencyController.php
│   │   ├── AgencyDashboardController.php
│   │   ├── AgencyRegistrationController.php
│   │   ├── AiAssistantController.php
│   │   ├── ApplicantAuthController.php
│   │   ├── ApplicantController.php
│   │   ├── ApplicantJobController.php
│   │   ├── ApplicantOtpAuthController.php
│   │   ├── ApplicantPortalController.php
│   │   ├── AuthController.php
│   │   ├── BillController.php
│   │   ├── CommissionController.php
│   │   ├── CommissionPaymentController.php
│   │   ├── CustomFieldDefinitionController.php
│   │   ├── DashboardController.php
│   │   ├── EmployerAuthController.php
│   │   ├── EmployerBillingController.php
│   │   ├── EmployerController.php
│   │   ├── EmployerDashboardController.php
│   │   ├── JobPositionController.php
│   │   ├── MarketingAgencyController.php
│   │   ├── MarketingAgentController.php
│   │   ├── NotificationController.php
│   │   ├── OfficialReceiptController.php
│   │   ├── PasswordResetController.php
│   │   ├── PaymentController.php
│   │   ├── PortalDocumentController.php
│   │   ├── ReportController.php
│   │   ├── ReportsIndexController.php
│   │   ├── SettingsController.php
│   │   ├── SubTableController.php
│   │   ├── UserController.php
│   │   └── Api/
│   │       └── CaseController.php
│   └── Middleware/
│       ├── AiQueryRateLimit.php
│       ├── CheckRole.php
│       ├── EnsureUserIsEmployer.php
│       ├── IdentifyAgency.php
│       ├── SecurityHeaders.php
│       └── TenantScope.php
├── Listeners/
│   ├── SendAgencyApprovalNotification.php
│   ├── SendBillCreatedNotification.php
│   ├── SendDocumentApprovalNotification.php
│   ├── SendDocumentRejectionNotification.php
│   ├── SendPaymentReceivedNotification.php
│   └── SendStatusChangeNotification.php
├── Models/
│   ├── ActivityLog.php
│   ├── Agency.php
│   ├── Applicant.php (+ 9 sub-models)
│   ├── Bill.php
│   ├── Cases.php
│   ├── Commission.php
│   ├── CommissionPayment.php
│   ├── Country.php
│   ├── CustomFieldDefinition.php
│   ├── CustomFieldValue.php
│   ├── Employer.php
│   ├── JobPosition.php
│   ├── MarketingAgency.php
│   ├── MarketingAgent.php
│   ├── Notification.php
│   ├── OfficialReceipt.php
│   ├── Payment.php
│   ├── Position.php
│   ├── StatusCode.php
│   ├── StatusTransition.php
│   ├── User.php
│   ├── UserPermission.php
│   └── Traits/ & Scopes/
├── Policies/
│   ├── AgencyPolicy.php
│   └── UserPolicy.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── EventServiceProvider.php
├── Services/
│   ├── SensitiveActionLogger.php
│   ├── StatusCodeService.php
│   └── StatusTransitionService.php
├── Traits/
│   └── LoginThrottle.php
└── helpers.php          # tenant_agency(), is_tenant_request()
```

### Frontend Views

All Blade templates under `resources/views/` organized by module:

```
resources/views/
├── accounting/          # Per-party accounting pages
├── agencies/            # Agency CRUD + branding
├── agency/              # Agency dashboard
├── applicants/          # Applicant CRUD + sub-forms/lists
├── auth/               # Login + password reset
├── bills/              # Billing management
├── commission-payments/
├── commissions/
├── custom-fields/
├── employer/           # Employer portal views
├── employers/          # Employer CRUD
├── job-positions/
├── layouts/            # Base layouts
├── marketing-agencies/
├── marketing-agents/
├── notifications/
├── official-receipts/
├── partials/           # Reusable Blade components
├── payments/
├── portal/             # Applicant portal views
├── reports/
├── settings/
├── transactions/
└── users/
```

Styling uses **Tailwind CSS 4** with **DaisyUI 5** components (no custom CSS files required).

---

## API & Routes

### Applicant Portal (`/portal/*`)

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/portal/register` | Registration form |
| POST | `/portal/register` | Submit registration |
| GET | `/portal/login` | Login form |
| POST | `/portal/login` | Login |
| GET | `/portal/login/otp` | OTP login form |
| POST | `/portal/login/otp/send` | Send OTP |
| POST | `/portal/login/otp/verify` | Verify OTP |
| GET | `/portal/dashboard` | Applicant dashboard |
| GET | `/portal/profile` | Applicant profile |
| GET | `/portal/jobs` | Browse jobs |
| GET | `/portal/jobs/{job}` | Job details |
| POST | `/portal/documents/upload` | Upload document |
| GET | `/portal/documents/{doc}/download` | Download document |

### Employer Portal (`/employer/*`)

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/employer/login` | Employer login |
| POST | `/employer/login` | Login |
| GET | `/employer/dashboard` | Employer dashboard |
| GET | `/employer/billing` | Billing overview |
| GET | `/employer/billing/soa` | Statement of Account |
| GET | `/employer/billing/applicant/{id}` | Per-applicant billing |

### Agency Management (`/agencies/*`)

Full CRUD with activate/deactivate, branding (logos, colors, favicon). Admin/super_admin only.

### Applicants (`/applicants/*`)

Full CRUD with sub-table management (education, work experience, skills, etc.), status updates, CSV export, SOA generation, and PDF resume export.

### Billing (`/bills/*`, `/payments/*`, `/official-receipts/*`)

Standard resource controllers with nested commission payment routes.

### Accounting (`/accounting/*`)

Read-only views aggregated per employer, worker, marketing agency, marketing agent, and recruitment agent.

### AI Assistant (`/ai/assistant/*`)

- `POST /ai/assistant/query` — Natural language → SQL (rate-limited)
- `GET /ai/assistant/templates` — Pre-built analytics templates
- `GET /ai/assistant/template/{template}` — Execute template query
- `GET /ai/assistant/export` — Export results as CSV

### Case Management API (`/api/cases/*`)

Full CRUD with search endpoint, rate-limited.

### Notifications (`/notifications/*`)

Unread counts, mark-as-read, mark-all-as-read.

### Reports (`/reports/*`)

PDF generation for applicants, bills, ORs, commissions, resumes, statistics.

---

## Portals

### 1. Internal Agency Dashboard

For agency staff with role-based access:
- **super_admin** — Full system access across all agencies
- **admin** — Agency-level admin
- **recruiter, staff, processor, coordinator, interviewer, manager, marketer, director** — Operational roles
- **billing** — Billing-specific access

### 2. Applicant Portal

Self-service portal for job applicants:
- Register with email/password or OTP
- Update profile
- Browse and view job postings
- Upload supporting documents

### 3. Employer Portal

Employer login with:
- Dashboard overview
- Billing & Statement of Account
- Per-applicant billing breakdown

---

## Testing

The project has extensive feature and unit tests. Run them with:

```bash
# Run all tests
php artisan test

# Or directly with PHPUnit
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit --testsuite=Feature
./vendor/bin/phpunit --testsuite=Unit
```

### Test Structure

```
tests/
├── Feature/
│   ├── AccountingAgentTest.php
│   ├── AccountingOverviewTest.php
│   ├── ApplicantExportTest.php
│   ├── ApplicantSoaTest.php
│   ├── ApplicantStatusUpdateTest.php
│   ├── SidebarNavigationTest.php
│   ├── TransactionHistoryTest.php
│   ├── Agency/          # Agency CRUD, branding, registration, tenant isolation
│   ├── AiAssistant/     # AI query, analytics
│   ├── Applicant/       # Create, edit, delete, search, CSV export, sub-tables
│   ├── Auth/            # Login rate limit, password reset, roles, user status
│   ├── Bill/            # CRUD
│   ├── CaseManagement/  # CRUD
│   ├── Commission/      # CRUD, commission payments
│   ├── Employer/        # Employer management
│   ├── JobPosition/     # Job position CRUD
│   ├── MarketingAgency/
│   ├── MarketingAgent/
│   ├── Notification/
│   ├── OfficialReceipt/
│   ├── Payment/
│   ├── Portal/          # Applicant portal flows
│   ├── Report/
│   ├── Security/
│   └── User/
├── Unit/
└── TestCase.php
```

Testing uses SQLite in-memory database for fast, isolated runs. Factories and seeders provide test data.

---

## Deployment

Deployment is handled via `deploy.sh`:

```bash
# Manual deployment
sudo ./deploy.sh

# Or deploy via the script
bash deploy.sh
```

The deployment script:
1. Pulls latest code from `master` branch
2. Installs PHP deps with `--no-dev --optimize-autoloader`
3. Runs database migrations
4. Caches config, routes, and views
5. Builds frontend assets (npm install + build)
6. Restarts queue workers

### Nginx / Apache

The webroot is `/var/www/agency.classapparelph.com/public/`. Ensure your web server points to this directory.

### Queue Workers

Queue workers run via Supervisor or systemd. After deployment, workers are restarted:

```bash
php artisan queue:restart
```

---

## Queue & Background Jobs

| Command | Purpose |
|---------|---------|
| `php artisan queue:work` | Process queue jobs |
| `php artisan queue:restart` | Gracefully restart workers after deploy |
| `CheckQueueHealth` command | Monitor queue health |

The app uses Redis as the queue driver by default, with database table fallback configured in `.env`.

### Cron Tasks

Add to crontab for scheduled tasks:

```
* * * * * cd /var/www/agency.classapparelph.com && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled commands:
- `CheckQueueHealth` — Monitors queue worker health
- `DatabaseBackup` — Automated database backup

---

## AI Assistant

A natural-language query interface that converts plain English questions into SQL queries against the application database.

### Endpoints

| URL | Method | Description |
|-----|--------|-------------|
| `/ai/assistant/query` | POST | Submit a natural language question |
| `/ai/assistant/templates` | GET | List pre-built analytics templates |
| `/ai/assistant/template/{template}` | GET | Execute a template query |
| `/ai/assistant/export` | GET | Export query results as CSV |

### Rate Limiting

- AI queries: 29 requests per minute (configurable via `AiQueryRateLimit` middleware)
- Standard login: 5 attempts per minute

---

## Multi-Tenancy

Agency Super supports multiple recruitment agencies on a single installation.

### How It Works

- Each agency has a `subdomain` field and its data is scoped via `agency_id`
- The `IdentifyAgency` middleware detects the subdomain from the request URL
- `TenantScope` middleware applies global scopes to automatically filter queries by the current agency
- The `tenant_agency()` helper returns the current agency instance (or null for super admin)
- Branding (logos, colors, favicon) is configurable per agency

### Super Admin Access

Super admins can access all agencies' data without tenant scoping.

---

## Contributing

### Code Style

This project uses Laravel Pint for PHP code style. Run before committing:

```bash
./vendor/bin/pint
```

### Development Workflow

```bash
# Start all dev services simultaneously
composer dev
```

This runs:
- `php artisan serve` — HTTP server
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Log viewer
- `npm run dev` — Vite hot reload

### Pull Requests

1. Create a feature branch from `master`
2. Write/update tests for your changes
3. Run `php artisan test` to confirm all pass
4. Run `./vendor/bin/pint` for code style
5. Submit a PR against `master`

---

## License

Proprietary — Toybits / Class Apparel PH.
