<?php

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $db = $config['db'];
        $dsn = "sqlsrv:Server={$db['host']},{$db['port']};Database={$db['database']};TrustServerCertificate=1";
        $this->pdo = new PDO($dsn, $db['username'], $db['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public static function scopeIdentity(PDO $pdo): int
    {
        $id = $pdo->query('SELECT CAST(SCOPE_IDENTITY() AS INT)')->fetchColumn();
        return (int) $id;
    }
}
