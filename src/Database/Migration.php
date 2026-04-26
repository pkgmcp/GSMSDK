<?php

declare(strict_types=1);

namespace GSMSDK\Database;

/**
 * Migration
 * 
 * Database migration system for schema management.
 */
abstract class Migration {
    protected Connection $connection;
    protected string $table;
    
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    
    /**
     * Run migration
     */
    abstract public function up(): void;
    
    /**
     * Rollback migration
     */
    abstract public function down(): void;
    
    /**
     * Get table name
     */
    public function getTable(): string {
        return $this->table;
    }
    
    /**
     * Create table schema
     */
    public function create(string $table, callable $callback): string {
        $this->table = $table;
        
        $schema = new Schema($this->connection);
        $callback($schema);
        
        return $schema->toCreateSql($table);
    }
    
    /**
     * Modify table schema
     */
    public function table(string $table, callable $callback): string {
        $this->table = $table;
        
        $schema = new Schema($this->connection);
        $callback($schema);
        
        return $schema->toAlterSql($table);
    }
    
    /**
     * Drop table
     */
    public function drop(string $table): string {
        return "DROP TABLE IF EXISTS {$table}";
    }
    
    /**
     * Drop table if exists
     */
    public function dropIfExists(string $table): string {
        return "DROP TABLE IF EXISTS {$table}";
    }
    
    /**
     * Rename table
     */
    public function rename(string $from, string $to): string {
        return "ALTER TABLE {$from} RENAME TO {$to}";
    }
    
    /**
     * Execute raw SQL
     */
    public function raw(string $sql): string {
        return $sql;
    }
    
    /**
     * Get PDO instance
     */
    protected function getPdo(): \PDO {
        $reflection = new \ReflectionClass($this->connection);
        $method = $reflection->getMethod('getPdo');
        $method->setAccessible(true);
        
        return $method->invoke($this->connection);
    }
}
