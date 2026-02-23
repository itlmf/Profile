# Windows IIS + SQL Server Local Deployment (Step-by-step)

This guide is for running the project on a Windows machine with IIS and local SQL Server.

## 1) Install prerequisites
1. **IIS**:
   - Windows Features → Internet Information Services
   - Enable: Web Server, CGI, Static Content, Default Document
2. **IIS URL Rewrite Module**:
   - Install Microsoft URL Rewrite (needed for `public/web.config`).
3. **PHP 8.1+ (NTS)**:
   - Put PHP in e.g. `C:\php`.
4. **SQL Server** + **SSMS**.
5. **ODBC Driver 18 for SQL Server**.
6. Enable PHP extensions in `php.ini`:
   - `extension=php_sqlsrv`
   - `extension=php_pdo_sqlsrv`

## 2) Configure IIS for PHP
1. Open **IIS Manager** → server node → **Handler Mappings**.
2. Add Module Mapping:
   - Request path: `*.php`
   - Module: `FastCgiModule`
   - Executable: `C:\php\php-cgi.exe`
   - Name: `PHP_via_FastCGI`
3. Restart IIS:
   - `iisreset`

## 3) Place project files
1. Copy project folder to, for example:
   - `C:\inetpub\ProfileProgram`
2. In IIS create a Site or Application:
   - Physical path must point to **`...\public`** folder
   - Example: `C:\inetpub\ProfileProgram\public`
3. App Pool:
   - `.NET CLR`: **No Managed Code**
   - Pipeline: Integrated

## 4) Configure environment (.env)
From **PowerShell** at project root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\windows\setup-env.ps1 `
  -DbHost "localhost" -DbPort "1433" -DbName "profile_program" `
  -DbUser "sa" -DbPass "YourStrong@Passw0rd" `
  -AppLocale "ar" -AppBaseUrl "http://localhost"
```

This generates `.env` from `.env.example`.

## 5) Create DB and run schema/seed
1. Create database `profile_program` in SSMS (or use existing DB).
2. Run from PowerShell:

SQL login:
```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\windows\init-db.ps1 `
  -Server "localhost" -Database "profile_program" -User "sa" -Password "YourStrong@Passw0rd"
```

Windows integrated auth:
```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\windows\init-db.ps1 `
  -Server "localhost" -Database "profile_program" -UseTrustedConnection
```

## 6) Start and verify
1. Start IIS site from IIS Manager.
2. Open browser:
   - `http://localhost/health`
3. Expected response:

```json
{"ok":true,"service":"profile-program-api"}
```

## 7) Common issues
- **404 on API routes**: URL Rewrite missing or site not pointing to `public`.
- **500 + SQL driver errors**: `php_sqlsrv` / `php_pdo_sqlsrv` not enabled or wrong PHP extension version.
- **Login failed**: check `.env` DB credentials and SQL Server auth mode.
- **Permission denied**: ensure IIS App Pool identity can read project files.

## 8) Useful endpoints
- `GET /health`
- `POST /api/trainees`
- `POST /api/trainees/bulk`
- `GET /api/trainees/search?q=...`
