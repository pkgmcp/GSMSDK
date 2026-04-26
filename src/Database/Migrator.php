<?php

declare(strict_types=1);

namespace GSMSDK\Database;

/**
 * Migrator
 * 
 * Runs database migrations.
 */
class Migrator {
    private Connection $connection;
    private array $migrations = [];
    private string $migrationTable = 'migrations';
    
    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->ensureMigrationTable();
    }
    
    /**
     * Add migration
     */
    public function addMigration(string $name, callable $migration): void {
        $this->migrations[$name] = $migration;
    }
    
    /**
     * Add migrations from directory
     */
    public function addMigrationsFromDirectory(string $directory): void {
        $files = glob($directory . '/*.php');
        
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $migration = require $file;
            
            if ($migration instanceof Migration) {
                $this->migrations[$name] = $migration;
            }
        }
    }
    
    /**
     * Run all pending migrations
     */
    public function migrate(): array {
        $results = [];
        $ran = $this->getRanMigrations();
        
        foreach ($this->migrations as $name => $migration) {
            if (!in_array($name, $ran)) {
                try {
                    $migration->up();
                    $this->logMigration($name);
                    $results[] = ['name' => $name, 'status' => 'success'];
                    echo "[OK] {$name}\n";
                } catch (\Exception $e) {
                    $results[] = ['name' => $name, 'status' => 'failed', 'error' => $e->getMessage()];
                    echo "[FAIL] {$name}: {$e->getMessage()}\n";
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Rollback last batch of migrations
     */
    public function rollback(): array {
        $results = [];
        $ran = $this->getRanMigrations();
        
        foreach (array_reverse($ran) as $name) {
            if (isset($this->migrations[$name])) {
                try {
                    $this->migrations[$name]->down();
                    $this->unlogMigration($name);
                    $results[] = ['name' => $name, 'status' => 'success'];
                    echo "[OK] {$name}\n";
                } catch (\Exception $e) {
                    $results[] = ['name' => $name, 'status' => 'failed', 'error' => $e->getMessage()];
                    echo "[FAIL] {$name}: {$e->getMessage()}\n";
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Rollback all migrations
     */
    public function reset(): array {
        $results = [];
        
        while ($ran = $this->getRanMigrations()) {
            $batchResults = $this->rollback();
            $results = array_merge($results, $batchResults);
            
            if (empty($batchResults)) {
                break;
            }
        }
        
        return $results;
    }
    
    /**
     * Get status of all migrations
     */
    public function status(): array {
        $ran = $this->getRanMigrations();
        $status = [];
        
        foreach ($this->migrations as $name => $migration) {
            $status[] = [
                'name' => $name,
                'ran' => in_array($name, $ran)
            ];
        }
        
        return $status;
    }
    
    /**
     * Ensure migrations table exists
     */
    private function ensureMigrationTable(): void {
        $pdo = $this->getPdo();
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
    
    /**
     * Log migration as run
     */
    private function logMigration(string $name): void {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("INSERT INTO {$this->migrationTable} (migration) VALUES (?)");
        $stmt->execute([$name]);
    }
    
    /**
     * Unlog migration
     */
    private function unlogMigration(string $name): void {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("DELETE FROM {$this->migrationTable} WHERE migration = ?");
        $stmt->execute([$name]);
    }
    
    /**
     * Get ran migrations
     */
    private function getRanMigrations(): array {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT migration FROM {$this->migrationTable} ORDER BY id");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
    
    /**
     * Get PDO instance
     */
    private function getPdo(): \PDO {
        $reflection = new \ReflectionClass($this->connection);
        $method = $reflection->getMethod('getPdo');
        $method->setAccessible(true);
        
        return $method->invoke($this->connection);
    }
}
