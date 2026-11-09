<?php
class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $host = 'pgsql-250294.srv.hoster.ru';
        $dbname = 'srv250294_brednia';
        $port = '5432';
        $user = 'srv250294_kdimka';
        $password = 'LM.OpSc6';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};";

        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        return $this->query($sql, $params)->fetch() ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
