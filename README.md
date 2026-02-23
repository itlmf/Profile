# Trainee Management Module (PHP + SQL Server)

This is a **drop-in module** you can merge into an existing PHP site.
It implements your core requirements for trainees, batches, attendance, weekly evaluations, and external integrations.

## What is included
- SQL Server schema: `db/schema.sql`
- PHP API router: `public/index.php`
- Services:
  - `src/TraineeService.php` (single add, bulk add, search)
  - `src/AttendanceService.php` (events + attendance + absence alerts)
  - `src/EvaluationService.php` (weekly scoring + cumulative percent)
  - `src/IntegrationService.php` (External SQL API + KoboToolbox)

## Requirements
- PHP 8.1+
- PDO SQL Server driver (`pdo_sqlsrv`)
- SQL Server

## Run locally
```bash
php -S 0.0.0.0:8000 -t public
```

## Deploy on another server
1. Create DB in target SQL Server.
2. Run `db/schema.sql` on target DB.
3. Copy this module into your existing site.
4. Set env vars (DB + integration tokens).
5. Route requests to `public/index.php` or merge endpoints into your existing router.

## Core business rules implemented
- Full name must be at least 4 parts.
- Egyptian national ID format + DOB extraction.
- Age is derived from birth date in DB.
- Volunteer date required if trainee is volunteer.
- Absence problem list if session absences exceed 3.
- Weekly evaluation from 5 criteria (1 to 5), cumulative percentage returned.

## Next phase you can add
- Authentication + role-based permissions screens (Admin, IT Admin, Mentor, Supervisor, Trainee).
- Arabic/English UI pages.
- PDF export for trainee profile.
- QR generation and scanner UI flow.
- Hidden sensitive exit reasons for authorized roles only.
