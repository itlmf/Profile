<?php

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $db = $config['db'];
        $dsn = "sqlsrv:Server={$db['host']},{$db['port']};Database={$db['database']}";
        $this->pdo = new PDO($dsn, $db['username'], $db['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
