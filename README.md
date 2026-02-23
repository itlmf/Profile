# Trainee Management Module (PHP + SQL Server)

This is a **drop-in module** you can merge into an existing PHP site.
It implements your core requirements for trainees, batches, attendance, weekly evaluations, and external integrations.

## What is included
- SQL Server schema: `db/schema.sql`
- SQL seed for Egyptian governorates: `db/seed_governorates.sql`
- PHP API router: `public/index.php`
- IIS rewrite config: `public/web.config`
- Windows deployment guide: `docs/windows-iis-deploy.md`
- Windows helper scripts:
  - `scripts/windows/setup-env.ps1`
  - `scripts/windows/init-db.ps1`
- `.env` support (`.env.example` included)

## Requirements
- PHP 8.1+
- PDO SQL Server driver (`pdo_sqlsrv`)
- SQL Server
- IIS URL Rewrite module (for IIS routing)

## Quick run (development)
```bash
php -S 0.0.0.0:8000 -t public
```

## Windows + IIS setup and start (quick)
1. Put project at `C:\inetpub\ProfileProgram`.
2. Point IIS site/app to `C:\inetpub\ProfileProgram\public`.
3. Create `.env`:
```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\windows\setup-env.ps1 -DbHost "localhost" -DbPort "1433" -DbName "profile_program" -DbUser "sa" -DbPass "YourStrong@Passw0rd"
```
4. Run DB scripts:
```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\windows\init-db.ps1 -Server "localhost" -Database "profile_program" -User "sa" -Password "YourStrong@Passw0rd"
```
5. Start IIS site and test:
- `http://localhost/health`

Detailed guide: `docs/windows-iis-deploy.md`

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
