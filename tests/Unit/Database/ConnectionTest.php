<?php

declare(strict_types=1);

namespace GSMSDK\Tests\Unit\Database;

use GSMSDK\Database\Connection;
use PHPUnit\Framework\TestCase;

/**
 * Database Connection Tests
 * 
 * Tests the Database connection setup and configuration.
 */
class ConnectionTest extends TestCase {
    /**
     * @test
     */
    public function it_can_be_instantiated_with_config(): void {
        $config = [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ];
        
        $connection = new Connection($config);
        
        $this->assertInstanceOf(Connection::class, $connection);
    }
    
    /**
     * @test
     */
    public function it_has_default_config(): void {
        $connection = new Connection();
        
        $config = $connection->getConfig();
        
        $this->assertEquals('mysql', $config['driver']);
        $this->assertEquals('127.0.0.1', $config['host']);
        $this->assertEquals('app', $config['database']);
        $this->assertEquals('root', $config['username']);
        $this->assertEquals('', $config['password']);
        $this->assertEquals('', $config['charset']);
        $this->assertEquals(0, $config['flags']);
    }
    
    /**
     * @test
     */
    public function it_throws_exception_for_unsupported_driver(): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported driver');
        
        $config = ['driver' => 'invalid_driver'];
        new Connection($config);
    }
    
    /**
     * @test
     */
    public function it_creates_mysql_dsn(): void {
        $config = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'test_db',
            'charset' => 'utf8mb4'
        ];
        
        $connection = new Connection($config);
        $reflection = new \ReflectionClass($connection);
        $method = $reflection->getMethod('getDsn');
        $method->setAccessible(true);
        
        $dsn = $method->invoke($connection);
        
        $this->assertStringContainsString('mysql', $dsn);
        $this->assertStringContainsString('localhost', $dsn);
        $this->assertStringContainsString('test_db', $dsn);
        $this->assertStringContainsString('utf8mb4', $dsn);
    }
    
    /**
     * @test
     */
    public function it_creates_pgsql_dsn(): void {
        $config = [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'database' => 'test_db'
        ];
        
        $connection = new Connection($config);
        $reflection = new \ReflectionClass($connection);
        $method = $reflection->getMethod('getDsn');
        $method->setAccessible(true);
        
        $dsn = $method->invoke($connection);
        
        $this->assertStringContainsString('pgsql', $dsn);
        $this->assertStringContainsString('host=localhost', $dsn);
        $this->assertStringContainsString('dbname=test_db', $dsn);
    }
    
    /**
     * @test
     */
    public function it_creates_sqlite_dsn(): void {
        $config = [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ];
        
        $connection = new Connection($config);
        $reflection = new \ReflectionClass($connection);
        $method = $reflection->getMethod('getDsn');
        $method->setAccessible(true);
        
        $dsn = $method->invoke($connection);
        
        $this->assertStringContainsString('sqlite', $dsn);
        $this->assertStringContainsString(':memory:', $dsn);
    }
    
    /**
     * @test
     */
    public function it_gets_config_key(): void {
        $config = ['driver' => 'mysql', 'host' => 'localhost'];
        $connection = new Connection($config);
        
        $this->assertEquals('mysql', $connection->getConfig('driver'));
        $this->assertEquals('localhost', $connection->getConfig('host'));
    }
    
    /**
     * @test
     */
    public function it_returns_default_for_missing_config_key(): void {
        $config = ['driver' => 'mysql'];
        $connection = new Connection($config);
        
        $this->assertEquals('default', $connection->getConfig('missing', 'default'));
    }
    
    /**
     * @test
     */
    public function it_returns_null_for_missing_config_key_without_default(): void {
        $config = ['driver' => 'mysql'];
        $connection = new Connection($config);
        
        $this->assertNull($connection->getConfig('missing'));
    }
    
    /**
     * @test
     */
    public function it_returns_all_config(): void {
        $config = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'test_db'
        ];
        $connection = new Connection($config);
        
        $allConfig = $connection->getConfig();
        
        $this->assertIsArray($allConfig);
        $this->assertEquals($config, array_intersect_key($allConfig, $config));
    }
    
    /**
     * @test
     */
    public function it_has_pdo_options_config(): void {
        $config = [
            'driver' => 'mysql',
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        ];
        $connection = new Connection($config);
        
        $conf = $connection->getConfig();
        
        $this->assertArrayHasKey('options', $conf);
        $this->assertEquals(\PDO::ERRMODE_EXCEPTION, $conf['options'][\PDO::ATTR_ERRMODE]);
    }
    
    /**
     * @test
     */
    public function it_uses_default_mysql_host(): void {
        $config = ['driver' => 'mysql'];
        $connection = new Connection($config);
        
        $this->assertEquals('127.0.0.1', $connection->getConfig('host'));
    }
    
    /**
     * @test
     */
    public function it_uses_default_mysql_database(): void {
        $config = ['driver' => 'mysql'];
        $connection = new Connection($config);
        
        $this->assertEquals('app', $connection->getConfig('database'));
    }
}
