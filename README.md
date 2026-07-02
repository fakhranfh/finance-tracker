# Finance Tracker

A personal finance tracking application built with Laravel, designed to help users record, organize, and understand their income and expenses.

The project is built on top of a Laravel 13 boilerplate that follows a strict **Repository Pattern** architecture and ships with a CRUD generator, server-side DataTables, and a full testing suite (Pest, Dusk, K6).

> [!NOTE]
> This project is in early development. Authentication and the core application shell are in place; finance-specific features (transactions, budgets, reports) are being built on top of this foundation.

## Tech Stack

| Layer | Package | Version |
|---|---|---|
| Runtime | PHP | 8.4 |
| Framework | Laravel | v13 |
| Auth | Laravel Fortify | v1 |
| Testing | Pest | v4 |
| Browser Tests | Laravel Dusk | — |
| Frontend | Tailwind CSS | v4 |
| Bundler | Vite | — |
| Code Style | Laravel Pint | v1 |

---

## Features

### Authentication

Full authentication flow powered by **Laravel Fortify**:

- **Registration** — with real-time password strength validation (uppercase, lowercase, number, symbol)
- **Login** — email + password with credential error feedback
- **Email Verification** — required before accessing protected routes; resend link supported
- **Forgot Password** — send reset link via email
- **Password Reset** — secure token-based reset with the same password policy as registration
- **Logout** — session termination with redirect to login

### Profile Management

Authenticated users can manage their account from the profile page:

- **Update profile** — change name and email address
- **Email change flow** — changing email triggers a re-verification step before the new address is applied
- **Change password** — requires current password; shows animated loading state and success/error feedback

### Application Architecture

All entities follow a strict **Repository Pattern**:

```
Controller → Service → Repository Interface → Repository Implementation
```

New CRUD modules (e.g. transactions, categories, budgets) can be scaffolded in seconds with the built-in generator:

```bash
php artisan make:rsc Transaction --label="Transactions"
```

This generates the model, migration, repository, service, controller, form requests, and Blade views (index, create, edit, show) with server-side DataTables wired up automatically.

---

## Quick Start

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- MySQL (or SQLite for local dev)

### Installation

```bash
git clone https://github.com/fakhranfh/finance-tracker
cd finance-tracker
composer install
npm install
```

### One-command setup

```bash
composer run setup
```

This copies `.env.example` → `.env`, generates the app key, runs migrations, and builds frontend assets.

### Start development servers

```bash
composer run dev
```

Starts the PHP server, queue listener, and Vite dev server concurrently.

---

## Common Commands

```bash
# Development
composer run dev          # Start all servers
php artisan pail          # Stream logs in real-time

# CRUD scaffolding
php artisan make:rsc ModelName --label="Label"
php artisan delete:rsc ModelName

# Testing
php artisan test --compact
php artisan dusk

# Code style
vendor/bin/pint --dirty   # Fix formatting on changed files

# Database
php artisan migrate
php artisan db:seed
```

---

## Environment Variables

Key variables to configure in `.env`:

```env
APP_NAME="Finance Tracker"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=finance-tracker

MAIL_MAILER=log        # Use 'smtp' in production
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

---

## License

MIT — see [LICENSE](LICENSE).
