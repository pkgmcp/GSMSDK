<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit\Database;

use GSMSDK\Database\Connection;
use GSMSDK\Database\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Query Builder Tests
 * 
 * Tests the fluent QueryBuilder for database operations.
 */
class QueryBuilderTest extends TestCase {
    private Connection $connection;
    private QueryBuilder $builder;
    
    protected function setUp(): void {
        // Use SQLite in-memory for testing
        $this->connection = new Connection([
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);
        
        $this->builder = new QueryBuilder($this->connection);
        
        // Create test table
        $pdo = $this->getPDO();
        $pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            age INTEGER,
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        
        // Insert test data
        $pdo->exec("INSERT INTO users (name, email, age, active) VALUES 
            ('Alice', 'alice@example.com', 25, 1),
            ('Bob', 'bob@example.com', 30, 1),
            ('Charlie', 'charlie@example.com', 35, 0),
            ('Diana', 'diana@example.com', 28, 1)
        ");
    }
    
    private function getPDO(): \PDO {
        $reflection = new \ReflectionClass($this->connection);
        $method = $reflection->getMethod('getPdo');
        $method->setAccessible(true);
        
        return $method->invoke($this->connection);
    }
    
    /**
     * @test
     */
    public function it_can_be_instantiated(): void {
        $this->assertInstanceOf(QueryBuilder::class, $this->builder);
    }
    
    /**
     * @test
     */
    public function it_selects_all_records(): void {
        $results = $this->builder->table('users')->get();
        
        $this->assertCount(4, $results);
    }
    
    /**
     * @test
     */
    public function it_selects_specific_columns(): void {
        $results = $this->builder->table('users')->select(['name', 'email'])->get();
        
        $this->assertObjectHasAttribute('name', $results[0]);
        $this->assertObjectHasAttribute('email', $results[0]);
        $this->assertObjectNotHasAttribute('age', $results[0]);
    }
    
    /**
     * @test
     */
    public function it_filters_with_where_clause(): void {
        $results = $this->builder->table('users')
            ->where('age', '>', 25)
            ->get();
        
        $this->assertCount(2, $results);
    }
    
    /**
     * @test
     */
    public function it_filters_with_where_equals(): void {
        $results = $this->builder->table('users')
            ->where('name', 'Alice')
            ->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('Alice', $results[0]->name);
    }
    
    /**
     * @test
     */
    public function it_filters_with_where_array(): void {
        $results = $this->builder->table('users')
            ->where([
                ['age', '>', 25],
                ['active', '=', 1]
            ])
            ->get();
        
        $this->assertCount(2, $results);
    }
    
    /**
     * @test
     */
    public function it_orders_results(): void {
        $results = $this->builder->table('users')
            ->orderBy('age', 'DESC')
            ->get();
        
        $this->assertEquals(35, $results[0]->age);
        $this->assertEquals(30, $results[1]->age);
    }
    
    /**
     * @test
     */
    public function it_limits_results(): void {
        $results = $this->builder->table('users')
            ->limit(2)
            ->get();
        
        $this->assertCount(2, $results);
    }
    
    /**
     * @test
     */
    public function it_limits_and_offsets_results(): void {
        $results = $this->builder->table('users')
            ->offset(1)
            ->limit(2)
            ->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('Bob', $results[0]->name);
    }
    
    /**
     * @test
     */
    public function it_finds_first_record(): void {
        $user = $this->builder->table('users')
            ->where('name', 'Alice')
            ->first();
        
        $this->assertEquals('Alice', $user->name);
    }
    
    /**
     * @test
     */
    public function it_returns_null_for_missing_record(): void {
        $user = $this->builder->table('users')
            ->where('name', 'Nonexistent')
            ->first();
        
        $this->assertNull($user);
    }
    
    /**
     * @test
     */
    public function it_counts_records(): void {
        $count = $this->builder->table('users')->count();
        
        $this->assertEquals(4, $count);
    }
    
    /**
     * @test
     */
    public function it_counts_with_conditions(): void {
        $count = $this->builder->table('users')
            ->where('active', 1)
            ->count();
        
        $this->assertEquals(3, $count);
    }
    
    /**
     * @test
     */
    public function it_inserts_record(): void {
        $result = $this->builder->table('users')->insert([
            'name' => 'Eve',
            'email' => 'eve@example.com',
            'age' => 22
        ]);
        
        $this->assertTrue($result);
        
        $user = $this->builder->table('users')
            ->where('name', 'Eve')
            ->first();
        
        $this->assertEquals('Eve', $user->name);
    }
    
    /**
     * @test
     */
    public function it_updates_records(): void {
        $result = $this->builder->table('users')
            ->where('name', 'Alice')
            ->update(['age' => 26]);
        
        $user = $this->builder->table('users')
            ->where('name', 'Alice')
            ->first();
        
        $this->assertEquals(26, $user->age);
    }
    
    /**
     * @test
     */
    public function it_deletes_records(): void {
        $result = $this->builder->table('users')
            ->where('name', 'Charlie')
            ->delete();
        
        $count = $this->builder->table('users')->count();
        
        $this->assertEquals(3, $count);
    }
    
    /**
     * @test
     */
    public function it_aggregates_with_sum(): void {
        $sum = $this->builder->table('users')
            ->where('active', 1)
            ->sum('age');
        
        $this->assertEquals(83, $sum);
    }
    
    /**
     * @test
     */
    public function it_aggregates_with_avg(): void {
        $avg = $this->builder->table('users')
            ->where('active', 1)
            ->avg('age');
        
        $this->assertEquals(27.666666666667, $avg, '', 0.001);
    }
    
    /**
     * @test
     */
    public function it_aggregates_with_max(): void {
        $max = $this->builder->table('users')->max('age');
        
        $this->assertEquals(35, $max);
    }
    
    /**
     * @test
     */
    public function it_aggregates_with_min(): void {
        $min = $this->builder->table('users')->min('age');
        
        $this->assertEquals(25, $min);
    }
    
    /**
     * @test
     */
    public function it_chains_multiple_wheres(): void {
        $results = $this->builder->table('users')
            ->where('age', '>', 25)
            ->where('active', 1)
            ->get();
        
        $this->assertCount(2, $results);
    }
    
    /**
     * @test
     */
    public function it_uses_or_where(): void {
        $results = $this->builder->table('users')
            ->where('name', 'Alice')
            ->orWhere('name', 'Bob')
            ->get();
        
        $this->assertCount(2, $results);
    }
    
    /**
     * @test
     */
    public function it_returns_table_name(): void {
        $tableName = $this->builder->table('users')->getTable();
        
        $this->assertEquals('users', $tableName);
    }
    
    /**
     * @test
     */
    public function it_generates_sql_select_all(): void {
        $sql = $this->builder->table('users')->toSql();
        
        $this->assertStringContainsString('SELECT', $sql);
        $this->assertStringContainsString('users', $sql);
    }
    
    /**
     * @test
     */
    public function it_generates_sql_with_conditions(): void {
        $sql = $this->builder->table('users')
            ->where('active', 1)
            ->toSql();
        
        $this->assertStringContainsString('WHERE', $sql);
        $this->assertStringContainsString('active', $sql);
    }
    
    /**
     * @test
     */
    public function it_handles_empty_table_name(): void {
        $this->expectException(\RuntimeException::class);
        
        $this->builder->get();
    }
    
    /**
     * @test
     */
    public function it_resets_queries(): void {
        $this->builder->table('users')->get();
        $this->builder->reset();
        
        $this->assertNull($this->builder->toSql());
    }
    
    /**
     * @test
     */
    public function it_builds_insert_sql(): void {
        $sql = $this->builder->table('users')
            ->toInsertSql(['name' => 'Test', 'email' => 'test@example.com']);
        
        $this->assertStringContainsString('INSERT', $sql);
        $this->assertStringContainsString('users', $sql);
    }
    
    /**
     * @test
     */
    public function it_builds_update_sql(): void {
        $sql = $this->builder->table('users')
            ->toUpdateSql(['name' => 'Updated']);
        
        $this->assertStringContainsString('UPDATE', $sql);
        $this->assertStringContainsString('users', $sql);
    }
    
    /**
     * @test
     */
    public function it_builds_delete_sql(): void {
        $sql = $this->builder->table('users')->toDeleteSql();
        
        $this->assertStringContainsString('DELETE', $sql);
        $this->assertStringContainsString('users', $sql);
    }
    
    /**
     * @test
     */
    public function it_plucks_column_values(): void {
        $names = $this->builder->table('users')->pluck('name');
        
        $this->assertCount(4, $names);
        $this->assertEquals('Alice', $names[0]);
    }
    
    /**
     * @test
     */
    public function it_plucks_key_value_pairs(): void {
        $map = $this->builder->table('users')->pluck('name', 'id');
        
        $this->assertIsArray($map);
        $this->assertArrayHasKey(1, $map);
        $this->assertEquals('Alice', $map[1]);
    }
}
