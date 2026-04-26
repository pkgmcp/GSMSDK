<?php

declare(strict_types=1);

namespace GSMSDK\Database;

use GSMSDK\Traits\Configurable;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Database Connection Manager
 */
class Connection
{
    use Configurable;

    private ?PDO $pdo = null;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => '',
            'username' => '',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ], $config);
    }

    /**
     * Get PDO instance
     */
    public function getPdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        try {
            $dsn = $this->buildDsn();
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Database connection failed: " . $e->getMessage(),
                0,
                $e
            );
        }

        return $this->pdo;
    }

    /**
     * Build DSN string
     */
    private function buildDsn(): string
    {
        $driver = $this->config['driver'];

        return match ($driver) {
            'mysql' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database']
            ),
            'sqlite' => sprintf('sqlite:%s', $this->config['database']),
            default => throw new RuntimeException("Unsupported driver: {$driver}"),
        };
    }

    /**
     * Execute query
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all results
     *
     * @return array<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single result
     *
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Insert record
     */
    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));

        return $this->getPdo()->lastInsertId();
    }

    /**
     * Update record
     */
    public function update(string $table, array $data, array $conditions, string $glue = 'AND'): int
    {
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $whereClause = implode(" {$glue} ", array_map(fn($k) => "{$k} = ?", array_keys($conditions)));

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
        $params = array_merge(array_values($data), array_values($conditions));

        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Delete record
     */
    public function delete(string $table, array $conditions, string $glue = 'AND'): int
    {
        $whereClause = implode(" {$glue} ", array_map(fn($k) => "{$k} = ?", array_keys($conditions)));

        $sql = "DELETE FROM {$table} WHERE {$whereClause}";

        return $this->query($sql, array_values($conditions))->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getPdo()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->getPdo()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->getPdo()->rollBack();
    }

    /**
     * Close connection
     */
    public function close(): void
    {
        $this->pdo = null;
    }
}
