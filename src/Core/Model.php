<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Database\Connection;
use GSMSDK\Database\QueryBuilder;
use GSMSDK\Traits\Configurable;

/**
 * Base Model class
 *
 * Provides ActiveRecord pattern for database interactions
 */
abstract class Model
{
    use Configurable;

    /** @var string Table name */
    protected string $table;

    /** @var string Primary key column */
    protected string $primaryKey = 'id';

    /** @var array<string, mixed> Model attributes */
    protected array $attributes = [];

    /** @var array<string, mixed> Original attributes */
    protected array $original = [];

    /** @var array<string, mixed> Dirty attributes */
    protected array $dirty = [];

    /** @var bool Whether model exists in database */
    protected bool $exists = false;

    /** @var Connection Database connection */
    protected ?Connection $connection = null;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->connection = $this->getConnection();
    }

    /**
     * Get database connection
     */
    protected function getConnection(): Connection
    {
        if ($this->connection === null) {
            $this->connection = new Connection($this->getConnectionConfig());
        }
        return $this->connection;
    }

    /**
     * Get connection configuration
     *
     * @return array<string, mixed>
     */
    protected function getConnectionConfig(): array
    {
        return [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'gsmsdk',
            'username' => 'root',
            'password' => '',
        ];
    }

    /**
     * Get query builder for model
     */
    protected function newQuery(): QueryBuilder
    {
        return (new QueryBuilder($this->connection))->table($this->table);
    }

    /**
     * Fill model with attributes
     *
     * @param  array<string, mixed>  $attributes
     */
    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        $this->original = $this->attributes;
        $this->dirty = [];
    }

    /**
     * Set attribute
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
        if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
            $this->dirty[$key] = $value;
        }
    }

    /**
     * Get attribute
     */
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Get all attributes
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Save model to database
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        }
        return $this->insert();
    }

    /**
     * Insert new record
     */
    protected function insert(): bool
    {
        try {
            $id = $this->newQuery()->insert($this->attributes);
            $this->setAttribute($this->primaryKey, $id);
            $this->exists = true;
            $this->original = $this->attributes;
            $this->dirty = [];
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Update existing record
     */
    protected function update(): bool
    {
        if (empty($this->dirty)) {
            return true;
        }

        try {
            $updated = $this->newQuery()
                ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
                ->update($this->dirty);

            if ($updated) {
                $this->original = $this->attributes;
                $this->dirty = [];
            }
            return $updated > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Delete record
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        try {
            $deleted = $this->newQuery()
                ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
                ->delete();

            if ($deleted) {
                $this->exists = false;
            }
            return $deleted > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Find record by ID
     *
     * @return static|null
     */
    public static function find(int $id): ?self
    {
        $model = new static();
        $result = $model->newQuery()
            ->where($model->primaryKey, $id)
            ->first();

        if ($result) {
            $model->fill($result);
            $model->exists = true;
            return $model;
        }
        return null;
    }

    /**
     * Get all records
     *
     * @return array<static>
     */
    public static function all(): array
    {
        $model = new static();
        $results = $model->newQuery()->get();

        return array_map(function ($data) use ($model) {
            $instance = new static();
            $instance->fill($data);
            $instance->exists = true;
            return $instance;
        }, $results);
    }

    /**
     * Create new record
     *
     * @param  array<string, mixed>  $attributes
     * @return static
     */
    public static function create(array $attributes): self
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Magic getter
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic isset
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}
