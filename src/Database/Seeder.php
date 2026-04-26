<?php

declare(strict_types=1);

namespace GSMSDK\Database;

/**
 * Seeder
 * 
 * Database seeder for populating tables with test data.
 */
abstract class Seeder {
    protected Connection $connection;
    protected array $factories = [];
    
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    
    /**
     * Run the seeder
     */
    abstract public function run(): void;
    
    /**
     * Call another seeder
     */
    protected function call(string $seederClass): void {
        if (!class_exists($seederClass)) {
            throw new \RuntimeException("Seeder class not found: {$seederClass}");
        }
        
        $seeder = new $seederClass($this->connection);
        $seeder->run();
    }
    
    /**
     * Call multiple seeders
     */
    protected function callMany(array $seederClasses): void {
        foreach ($seederClasses as $seederClass) {
            $this->call($seederClass);
        }
    }
    
    /**
     * Create factory instance
     */
    protected function factory(string $modelClass): Factory {
        $factoryClass = $this->resolveFactoryClass($modelClass);
        
        if (!class_exists($factoryClass)) {
            throw new \RuntimeException("Factory class not found: {$factoryClass}");
        }
        
        return new $factoryClass();
    }
    
    /**
     * Resolve factory class name from model class
     */
    private function resolveFactoryClass(string $modelClass): string {
        $modelNamespace = 'GSMSDK\\Models';
        $factoryNamespace = 'GSMSDK\\Database\\Factories';
        
        $modelName = basename(str_replace('\\', '/', $modelClass));
        
        return "{$factoryNamespace}\\{$modelName}Factory";
    }
    
    /**
     * Truncate table
     */
    protected function truncate(string $table): void {
        $pdo = $this->getPdo();
        $pdo->exec("DELETE FROM {$table}");
        
        // Reset auto-increment for SQLite
        $pdo->exec("DELETE FROM sqlite_sequence WHERE name='{$table}'");
    }
    
    /**
     * Insert data into table
     */
    protected function insert(string $table, array $data): void {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") ";
        $sql .= "VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->getPdo()->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
    }
    
    /**
     * Insert multiple rows
     */
    protected function insertMany(string $table, array $rows): void {
        foreach ($rows as $row) {
            $this->insert($table, $row);
        }
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
