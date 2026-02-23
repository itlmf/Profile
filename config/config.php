<?php

require_once __DIR__ . '/../src/helpers.php';
load_env_file(__DIR__ . '/../.env');

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '1433',
        'database' => getenv('DB_NAME') ?: 'profile_program',
        'username' => getenv('DB_USER') ?: 'sa',
        'password' => getenv('DB_PASS') ?: 'YourStrong@Passw0rd',
    ],
    'app' => [
        'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost',
        'default_locale' => getenv('APP_LOCALE') ?: 'ar',
    ],
    'integrations' => [
        'external_sql_api_base' => getenv('EXTERNAL_SQL_API_BASE') ?: '',
        'external_sql_api_token' => getenv('EXTERNAL_SQL_API_TOKEN') ?: '',
        'kobo_base' => getenv('KOBO_BASE') ?: 'https://kf.kobotoolbox.org',
        'kobo_token' => getenv('KOBO_TOKEN') ?: '',
        'kobo_asset_uid' => getenv('KOBO_ASSET_UID') ?: '',
    ]
];
