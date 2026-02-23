# Trainee Management Module (PHP + SQL Server)

This is a **drop-in module** you can merge into an existing PHP site.
It implements your core requirements for trainees, batches, attendance, weekly evaluations, and external integrations.

## What is included
- SQL Server schema: `db/schema.sql`
- SQL seed for Egyptian governorates: `db/seed_governorates.sql`
- PHP API router: `public/index.php`
- IIS rewrite config: `public/web.config`
- Windows deployment guide: `docs/windows-iis-deploy.md`
- `.env` support (`.env.example` included)

## Requirements
- PHP 8.1+
- PDO SQL Server driver (`pdo_sqlsrv`)
- SQL Server

## Quick run (development)
```bash
php -S 0.0.0.0:8000 -t public
```

## Windows + IIS deployment (local SQL Server)
1. Copy project to server.
2. Copy `.env.example` to `.env` and set DB values.
3. Point IIS site root to `public/`.
4. Ensure URL Rewrite module is installed (for `public/web.config`).
5. Run SQL scripts in this order:
   - `db/schema.sql`
   - `db/seed_governorates.sql`

Detailed steps: `docs/windows-iis-deploy.md`

## Core business rules implemented
- Full name must be at least 4 parts.
- Egyptian national ID format + DOB extraction.
- Age is derived from birth date in DB.
- Volunteer date required if trainee is volunteer.
- Absence problem list if session absences exceed 3.
- Weekly evaluation from 5 criteria (1 to 5), cumulative percentage returned.

## Important implementation notes
- SQL Server identity IDs are returned using `SCOPE_IDENTITY()` (more reliable with sqlsrv on Windows/IIS).
- Router path extraction supports IIS rewrite and `PATH_INFO`.
