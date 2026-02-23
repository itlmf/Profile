# Windows IIS + SQL Server Local Deployment

## 1) Install required components
1. IIS with URL Rewrite module.
2. PHP 8.1+ (NTS build recommended for FastCGI).
3. Microsoft ODBC Driver 18 for SQL Server.
4. PHP extensions: `php_sqlsrv` and `php_pdo_sqlsrv` matching your PHP version.

## 2) Prepare project
1. Copy project folder to Windows server.
2. Copy `.env.example` to `.env` and set DB credentials.
3. Ensure IIS site points to project `public` directory.

## 3) IIS settings
- App Pool: `No Managed Code`.
- Enable `Read/Script` permissions.
- Confirm `public/web.config` is loaded.

## 4) Create database and schema
Run on local SQL Server:
- `db/schema.sql`
- `db/seed_governorates.sql`

## 5) Verify API
- `GET /health`
- `POST /api/trainees`

## Notes
- This project uses `SCOPE_IDENTITY()` for SQL Server identity retrieval (IIS + sqlsrv-safe).
- If your IIS site is not rooted at `public`, use a virtual directory pointing to `public`.
