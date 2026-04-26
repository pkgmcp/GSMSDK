<?php

declare(strict_types=1);

namespace GSMSDK\Database;

/**
 * Schema Builder
 * 
 * Fluent schema builder for creating and modifying database tables.
 */
class Schema {
    private Connection $connection;
    private array $columns = [];
    private array $indexes = [];
    private array $foreignKeys = [];
    private array $modifications = [];
    private ?string $operation = null;
    
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    
    /**
     * Create a new big integer column (auto-increment)
     */
    public function id(string $column = 'id'): self {
        return $this->bigIncrements($column);
    }
    
    /**
     * Create a new big integer column (auto-increment)
     */
    public function bigIncrements(string $column): self {
        $this->addColumn('bigInteger', $column, ['autoIncrement' => true, 'primary' => true]);
        return $this;
    }
    
    /**
     * Create a new integer column (auto-increment)
     */
    public function increments(string $column): self {
        $this->addColumn('integer', $column, ['autoIncrement' => true, 'primary' => true]);
        return $this;
    }
    
    /**
     * Create a new string column
     */
    public function string(string $column, int $length = 255): self {
        $this->addColumn('string', $column, ['length' => $length]);
        return $this;
    }
    
    /**
     * Create a new text column
     */
    public function text(string $column): self {
        $this->addColumn('text', $column);
        return $this;
    }
    
    /**
     * Create a new integer column
     */
    public function integer(string $column): self {
        $this->addColumn('integer', $column);
        return $this;
    }
    
    /**
     * Create a new big integer column
     */
    public function bigInteger(string $column): self {
        $this->addColumn('bigInteger', $column);
        return $this;
    }
    
    /**
     * Create a new small integer column
     */
    public function smallInteger(string $column): self {
        $this->addColumn('smallInteger', $column);
        return $this;
    }
    
    /**
     * Create a new tiny integer column
     */
    public function tinyInteger(string $column): self {
        $this->addColumn('tinyInteger', $column);
        return $this;
    }
    
    /**
     * Create a new unsigned big integer column
     */
    public function unsignedBigInteger(string $column): self {
        $this->addColumn('bigInteger', $column, ['unsigned' => true]);
        return $this;
    }
    
    /**
     * Create a new unsigned integer column
     */
    public function unsignedInteger(string $column): self {
        $this->addColumn('integer', $column, ['unsigned' => true]);
        return $this;
    }
    
    /**
     * Create a new decimal column
     */
    public function decimal(string $column, int $total = 8, int $places = 2): self {
        $this->addColumn('decimal', $column, ['total' => $total, 'places' => $places]);
        return $this;
    }
    
    /**
     * Create a new float column
     */
    public function float(string $column, int $total = 8, int $places = 2): self {
        $this->addColumn('float', $column, ['total' => $total, 'places' => $places]);
        return $this;
    }
    
    /**
     * Create a new double column
     */
    public function double(string $column): self {
        $this->addColumn('double', $column);
        return $this;
    }
    
    /**
     * Create a new boolean column
     */
    public function boolean(string $column): self {
        $this->addColumn('boolean', $column);
        return $this;
    }
    
    /**
     * Create a new date column
     */
    public function date(string $column): self {
        $this->addColumn('date', $column);
        return $this;
    }
    
    /**
     * Create a new dateTime column
     */
    public function dateTime(string $column): self {
        $this->addColumn('dateTime', $column);
        return $this;
    }
    
    /**
     * Create a new time column
     */
    public function time(string $column): self {
        $this->addColumn('time', $column);
        return $this;
    }
    
    /**
     * Create a new timestamp column
     */
    public function timestamp(string $column): self {
        $this->addColumn('timestamp', $column);
        return $this;
    }
    
    /**
     * Add timestamps columns
     */
    public function timestamps(): self {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
        return $this;
    }
    
    /**
     * Add soft deletes column
     */
    public function softDeletes(): self {
        $this->timestamp('deleted_at')->nullable();
        return $this;
    }
    
    /**
     * Create a new JSON column
     */
    public function json(string $column): self {
        $this->addColumn('json', $column);
        return $this;
    }
    
    /**
     * Create a new UUID column
     */
    public function uuid(string $column): self {
        $this->addColumn('uuid', $column);
        return $this;
    }
    
    /**
     * Create a new IP address column
     */
    public function ipAddress(string $column): self {
        $this->string($column, 45);
        return $this;
    }
    
    /**
     * Create a new MAC address column
     */
    public function macAddress(string $column): self {
        $this->string($column, 17);
        return $this;
    }
    /**
     * Set column as nullable
     */
    public function nullable(): self {
        $lastColumn = $this->getLastColumn();
        if ($lastColumn) {
            $lastColumn['nullable'] = true;
        }
        return $this;
    }
    
    /**
     * Set column default value
     */
    public function default(mixed $value): self {
        $lastColumn = $this->getLastColumn();
        if ($lastColumn) {
            $lastColumn['default'] = $value;
        }
        return $this;
    }
    
    /**
     * Set column as primary key
     */
    public function primary(): self {
        $lastColumn = $this->getLastColumn();
        if ($lastColumn) {
            $lastColumn['primary'] = true;
        }
        return $this;
    }
    
    /**
     * Set column as unique
     */
    public function unique(): self {
        $lastColumn = $this->getLastColumn();
        if ($lastColumn) {
            $this->indexes[] = [
                'type' => 'unique',
                'columns' => [$lastColumn['name']]
            ];
        }
        return $this;
    }
    
    /**
     * Set column as index
     */
    public function index(): self {
        $lastColumn = $this->getLastColumn();
        if ($lastColumn) {
            $this->indexes[] = [
                'type' => 'index',
                'columns' => [$lastColumn['name']]
            ];
        }
        return $this;
    }
    
    /**
     * Add foreign key constraint
     */
    public function foreignId(string $column): self {
        $this->unsignedBigInteger($column);
        return $this;
    }
    
    /**
     * Add foreign key constraint with references
     */
    public function foreign(string $column): self {
        $this->foreignKeys[] = [
            'column' => $column,
            'references' => null,
            'on' => null
        ];
        return $this;
    }
    
    /**
     * Specify referenced table and column
     */
    public function references(string $column): self {
        $lastKey = count($this->foreignKeys) - 1;
        if (isset($this->foreignKeys[$lastKey])) {
            $this->foreignKeys[$lastKey]['references'] = $column;
        }
        return $this;
    }
    
    /**
     * Specify foreign table
     */
    public function on(string $table): self {
        $lastKey = count($this->foreignKeys) - 1;
        if (isset($this->foreignKeys[$lastKey])) {
            $this->foreignKeys[$lastKey]['on'] = $table;
        }
        return $this;
    }
    
    /**
     * Add remember token
     */
    public function rememberToken(): self {
        $this->string('remember_token', 100)->nullable();
        return $this;
    }
    
    /**
     * Add polymorphic columns
     */
    public function morphs(string $name): self {
        $this->unsignedBigInteger("{$name}_id");
        $this->string("{$name}_type");
        return $this;
    }
    
    /**
     * Add nullable morphs
     */
    public function nullableMorphs(string $name): self {
        $this->unsignedBigInteger("{$name}_id")->nullable();
        $this->string("{$name}_type")->nullable();
        return $this;
    }
    /**
     * Add column modification (drop, rename, change)
     */
    public function dropColumn(string $column): self {
        $this->modifications[] = [
            'type' => 'dropColumn',
            'column' => $column
        ];
        return $this;
    }
    
    /**
     * Rename column
     */
    public function renameColumn(string $from, string $to): self {
        $this->modifications[] = [
            'type' => 'renameColumn',
            'from' => $from,
            'to' => $to
        ];
        return $this;
    }
    
    /**
     * Generate CREATE TABLE SQL
     */
    public function toCreateSql(string $table): string {
        $lines = [];
        
        foreach ($this->columns as $column) {
            $lines[] = "  {$this->buildColumnDefinition($column)}";
        }
        
        // Add primary key constraint if not already defined
        $primaryColumns = array_filter($this->columns, fn($c) => $c['primary'] ?? false);
        if (count($primaryColumns) > 1) {
            $primaryNames = array_map(fn($c) => $c['name'], $primaryColumns);
            $lines[] = "  PRIMARY KEY (" . implode(', ', $primaryNames) . ")";
        }
        
        $sql = "CREATE TABLE {$table} (\n";
        $sql .= implode(",\n", $lines);
        $sql .= "\n)";
        
        return $sql;
    }
    
    /**
     * Generate ALTER TABLE SQL
     */
    public function toAlterSql(string $table): string {
        $statements = [];
        
        foreach ($this->modifications as $mod) {
            switch ($mod['type']) {
                case 'dropColumn':
                    $statements[] = "ALTER TABLE {$table} DROP COLUMN {$mod['column']}";
                    break;
                case 'renameColumn':
                    $statements[] = "ALTER TABLE {$table} RENAME COLUMN {$mod['from']} TO {$mod['to']}";
                    break;
            }
        }
        
        foreach ($this->columns as $column) {
            $statements[] = "ALTER TABLE {$table} ADD COLUMN {$this->buildColumnDefinition($column)}";
        }
        
        foreach ($this->indexes as $index) {
            $name = "idx_{$table}_" . implode('_', $index['columns']);
            $columns = implode(', ', $index['columns']);
            
            if ($index['type'] === 'unique') {
                $statements[] = "ALTER TABLE {$table} ADD CONSTRAINT {$name} UNIQUE ({$columns})";
            } else {
                $statements[] = "CREATE INDEX {$name} ON {$table} ({$columns})";
            }
        }
        
        return implode(";\n", $statements);
    }
    
    /**
     * Add column definition
     */
    private function addColumn(string $type, string $name, array $options = []): void {
        $this->columns[] = array_merge([
            'type' => $type,
            'name' => $name
        ], $options);
    }
    
    /**
     * Get last column reference
     */
    private function &getLastColumn(): ?array {
        $count = count($this->columns);
        if ($count > 0) {
            return $this->columns[$count - 1];
        }
        
        $null = null;
        return $null;
    }
    
    /**
     * Build column definition SQL
     */
    private function buildColumnDefinition(array $column): string {
        $definition = "{$column['name']} {$this->getTypeSql($column['type'], $column)}";
        
        if (!($column['nullable'] ?? false)) {
            $definition .= " NOT NULL";
        }
        
        if (array_key_exists('default', $column)) {
            $value = $column['default'];
            if ($value === null) {
                $definition .= " DEFAULT NULL";
            } elseif (is_string($value)) {
                $definition .= " DEFAULT '{$value}'";
            } elseif (is_bool($value)) {
                $definition .= " DEFAULT " . ($value ? '1' : '0');
            } else {
                $definition .= " DEFAULT {$value}";
            }
        }
        
        if ($column['primary'] ?? false) {
            $definition .= " PRIMARY KEY";
        }
        
        if ($column['autoIncrement'] ?? false) {
            $definition .= " AUTOINCREMENT";
        }
        
        return $definition;
    }
    
    /**
     * Get SQL type string
     */
    private function getTypeSql(string $type, array $column): string {
        return match ($type) {
            'bigInteger' => 'BIGINT',
            'integer' => 'INTEGER',
            'smallInteger' => 'SMALLINT',
            'tinyInteger' => 'TINYINT',
            'string' => "VARCHAR({$column['length']})",
            'text' => 'TEXT',
            'decimal' => "DECIMAL({$column['total']}, {$column['places']})",
            'float' => 'FLOAT',
            'double' => 'DOUBLE',
            'boolean' => 'BOOLEAN',
            'date' => 'DATE',
            'dateTime' => 'DATETIME',
            'time' => 'TIME',
            'timestamp' => 'TIMESTAMP',
            'json' => 'JSON',
            'uuid' => 'UUID',
            default => 'TEXT'
        };
    }
}
