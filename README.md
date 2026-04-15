# Multi-Tenant SaaS Boilerplate

A production-ready multi-tenant SaaS starter kit built with **Laravel 12**, **Vue 3**, **Inertia.js**, **PostgreSQL**, and **Stripe Cashier**.

---

## Features

- **Multi-tenancy** — Single-database, organisation-scoped rows via `OrganizationScope` global scope
- **Organisation management** — Create, rename, delete organisations; switch between them from the nav
- **Team members** — Invite by email, assign roles (owner / admin / member), remove members
- **Role-based access** — Powered by Spatie Laravel Permission; policies enforce every controller action
- **Subscription billing** — Stripe Cashier integration; plan cards UI; subscribe / cancel; webhook handler
- **Activity logging** — Every model mutation is logged per-organisation via Spatie ActivityLog v5
- **Queue workers** — Redis + Laravel Horizon; ready for background jobs
- **Dev tools** — Laravel Telescope and Debugbar pre-installed (dev-only)
- **Tests** — 36 Pest tests covering tenant isolation, registration, billing, and activity logging

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.4) |
| Frontend | Vue 3 + Inertia.js v2 |
| Auth / Scaffolding | Laravel Breeze |
| Database | PostgreSQL 16 |
| Cache / Queue | Redis |
| Queue dashboard | Laravel Horizon |
| Roles & Permissions | Spatie Laravel Permission v7 |
| Activity logging | Spatie ActivityLog v5 |
| Subscriptions | Laravel Cashier v16 (Stripe) |
| Tests | Pest PHP |
| Dev tools | Laravel Telescope + Debugbar |

---

## Prerequisites

- **PHP 8.4** (via [Laravel Herd](https://herd.laravel.com) recommended)
- **Composer 2**
- **Node.js 20** + npm 10
- **PostgreSQL 16**
- **Redis** (Homebrew: `brew install redis && brew services start redis`)

---

## Quick Start

### 1. Clone & install dependencies

```bash
git clone https://github.com/sipcode5/multi-tenant-saas.git multi-tenant-saas
cd multi-tenant-saas
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set at minimum:

```env
DB_DATABASE=multitenant_saas
DB_USERNAME=<your_postgres_user>
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

### 3. Set up the database

```bash
# Create the database (if it doesn't exist)
createdb multitenant_saas

# Run migrations and seed demo data
php artisan migrate --seed
```

Seeds will create:
- Roles: `owner`, `admin`, `member`
- Plans: Starter ($9/mo), Pro ($29/mo), Enterprise ($99/mo)

### 4. Start development services

Open **two terminal tabs**:

```bash
# Tab 1 — Frontend (Vite dev server)
npm run dev

# Tab 2 — Queue worker (Horizon)
php artisan horizon
```

If using **Laravel Herd**, the app is served automatically at `http://multi-tenant-saas.test`.  
Otherwise:

```bash
php artisan serve   # http://localhost:8000
```

---

## Important URLs

| URL | Description |
|---|---|
| `/register` | Create your first account (auto-creates an organization) |
| `/dashboard` | Overview with quick links |
| `/organizations/{slug}/members` | Manage team members |
| `/billing` | View plans and subscribe |
| `/activity-log` | Audit trail for the organization |
| `/horizon` | Queue dashboard |
| `/telescope` | Request inspector (dev only) |

---

## Running Tests

The test suite uses a separate PostgreSQL database.

```bash
# Create the test database (once)
createdb multitenant_saas_testing

# Run all tests
php artisan test

# Run a specific group
php artisan test --filter TenantIsolationTest
```

Expected output: **36 tests, 120 assertions — all passing**.

---

## Project Structure (key files)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── OrganizationController.php        # CRUD for organisations
│   │   ├── OrganizationMemberController.php  # Invite / remove / role
│   │   ├── SwitchOrganizationController.php  # Tenant switcher
│   │   ├── BillingController.php             # Plans & Stripe
│   │   └── ActivityLogController.php         # Audit log
│   └── Middleware/
│       ├── SetCurrentOrganization.php        # Resolves active org per request
│       ├── EnsureOrganizationActive.php      # Blocks suspended orgs
│       └── EnsureSubscribed.php             # Gating paid features
├── Models/
│   ├── Organization.php   # Billable, LogsActivity, SoftDeletes
│   ├── User.php           # HasRoles (Spatie), relationships
│   ├── Plan.php           # Stripe plans
│   └── Traits/
│       ├── HasOrganization.php             # Boots OrganizationScope
│       └── LogsOrganizationActivity.php   # Standardized log options
├── Policies/
│   └── OrganizationPolicy.php             # Authorization gates
├── Scopes/
│   └── OrganizationScope.php              # Global tenant filter
└── Services/
    ├── OrganizationService.php            # Org creation & invitations
    ├── BillingService.php                 # Stripe subscribe/cancel
    └── ActivityService.php               # Explicit activity logging

resources/js/
├── Components/
│   └── OrganizationSwitcher.vue           # Dropdown nav component
├── Layouts/
│   └── AuthenticatedLayout.vue            # Main app shell + flash messages
└── Pages/
    ├── Dashboard.vue
    ├── Organizations/
    │   ├── Create.vue
    │   ├── Settings.vue
    │   └── Members.vue
    ├── Billing/
    │   └── Index.vue
    └── ActivityLog/
        └── Index.vue
```

---

## Stripe Integration Notes

This boilerplate ships with **mock Stripe keys** (`pk_test_REPLACE_ME`). For real payments:

1. Create a Stripe account at [stripe.com](https://stripe.com)
2. Replace `STRIPE_KEY` and `STRIPE_SECRET` in `.env` with your test keys
3. Update `stripe_price_id` values in `database/seeders/PlanSeeder.php`
4. Configure your Stripe webhook endpoint to `https://yourdomain.com/stripe/webhook`

The `BillingService::subscribe()` falls back to a local mock subscription when no Stripe `payment_method` ID is provided — useful for testing the UI without a real Stripe session.

---

## Environment Variables Reference

| Variable | Description |
|---|---|
| `APP_NAME` | Application name |
| `APP_URL` | Base URL (e.g. `http://multi-tenant-saas.test`) |
| `DB_CONNECTION` | `pgsql` |
| `DB_DATABASE` | PostgreSQL database name |
| `SESSION_DRIVER` | `redis` (recommended) |
| `QUEUE_CONNECTION` | `redis` |
| `CACHE_STORE` | `redis` |
| `STRIPE_KEY` | Stripe publishable key |
| `STRIPE_SECRET` | Stripe secret key |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook signing secret |
| `TELESCOPE_ENABLED` | `true` in dev, `false` in production |

---

## License

MIT

