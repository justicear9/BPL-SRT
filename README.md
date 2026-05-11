# BPL Sales Rep Tracking Software (BPL-SRT)

BPL-SRT is a Laravel-based field sales tracking system for pharmaceutical reps and managers.  
It helps teams manage customers, visits, orders, collections, product samples, and reporting in one place.

## What This Project Does

- Manages customer records and assigned sales reps.
- Tracks field visits with required contact persons.
- Records order lines, samples given, and collections received during visits.
- Supports customer import from CSV/XLSX with optional auto-assignment or auto-creation of sales rep users.
- Supports product import from `Items.xlsx`.
- Provides admin and workspace flows for day-to-day operations.

## Tech Stack

- Laravel 12 (PHP 8.3+)
- MySQL / MariaDB
- Vite for frontend assets
- Filament (admin panel/login)

## Getting Started

### 1) Requirements

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL or MariaDB

### 2) Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 3) Configure Database

Edit `.env` and set:

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### 4) Run Migrations

```bash
php artisan migrate
```

### 5) Seed Demo Data (optional but useful)

```bash
php artisan db:seed --class=Database\\Seeders\\DemoSeeder
```

This creates demo users:

- Admin: `admin` / `password`
- Sales rep: `rep` / `password`

### 6) Build and Run

```bash
npm run build
php artisan serve
```

For local frontend development:

```bash
npm run dev
```

## Login

Guest redirects go to the Filament login route (`/admin/login`).  
After successful login, users are redirected to the sales dashboard/workspace.

The login field accepts username or email.

## Imports

### Customer Import

Use the workspace customer import page or importer service.  
Expected columns include:

- `name` (required)
- `type` (required)
- `phone`, `city`, `region`, `address_line`, `shop_latitude`, `shop_longitude` (optional)
- `assigned_user_email` (optional)

`assigned_user_email` behavior:

- If it matches an existing user email/username, customer is assigned to that user.
- If it is a plain label (e.g., `LAWRENCE`), a sales rep account is auto-created:
  - username derived from label
  - email like `username@customers.import`
  - default password `password`

### Product Import

Import products from `Items.xlsx`:

```bash
php artisan products:import-items Items.xlsx
```

The command upserts products using SKU (`No.` column) and updates name/category/UOM.

## Useful Commands

```bash
# Run tests
php artisan test

# Format PHP code
vendor/bin/pint

# Rebuild frontend assets
npm run build
```

## Project Structure (High Level)

- `app/Http/Controllers/Workspace` - workspace CRUD and import endpoints
- `app/Services/Imports` - customer spreadsheet import logic
- `app/Console/Commands` - spreadsheet product import command
- `app/Filament` - Filament auth/resources/widgets
- `resources/views/content/workspace` - workspace UI pages
- `resources/assets/js` - frontend behavior (visit form, modals, etc.)

## Notes

- `.env`, database dumps, and large local import files should not be committed.
- If data appears missing, verify `.env` points to the expected database before running migration/reset commands.

## License

This project is currently maintained as a private internal application.
