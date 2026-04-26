<?php

declare(strict_types=1);

namespace GSMSDK\Core;

use GSMSDK\Database\QueryBuilder;
use GSMSDK\Database\Connection;

/**
 * Base Model
 * 
 * ActiveRecord-style base model for database operations.
 */
abstract class Model {
    protected static ?string $table = null;
    protected static ?string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $casts = [];
    protected static array $hidden = [];
    protected static array $dates = [];
    
    protected array $attributes = [];
    protected array $original = [];
    protected array $relations = [];
    protected bool $exists = false;
    protected ?Connection $connection = null;
    
    /**
     * Constructor
     */
    public function __construct(array $attributes = []) {
        $this->connection = new Connection(\{\$this->app->config('database')\});
        $this->fill($attributes);
    }
    
    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): void {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable) || empty(static::$fillable)) {
                $this->attributes[$key] = $this->castAttribute($key, $value);
            }
        }
        
        $this->original = $this->attributes;
    }
    
    /**
     * Get table name
     */
    public static function getTable(): string {
        if (static::$table) {
            return static::$table;
        }
        
        $className = basename(str_replace('\\', '/', static::class));
        return strtolower($className) . 's';
    }
    
    /**
     * Get primary key
     */
    public static function getKeyName(): string {
        return static::$primaryKey;
    }
    
    /**
     * Get all records
     */
    public static function all(array $columns = ['*']): array {
        $instance = new static();
        $builder = $instance->newQuery();
        
        return $builder->select($columns)->get();
    }
    
    /**
     * Find record by primary key
     */
    public static function find(mixed $id): ?static {
        $instance = new static();
        $builder = $instance->newQuery();
        
        $result = $builder->where(static::$primaryKey, $id)->first();
        
        if ($result) {
            $model = new static((array)$result);
            $model->exists = true;
            $model->original = $model->attributes;
            return $model;
        }
        
        return null;
    }
    
    /**
     * Find record or throw exception
     */
    public static function findOrFail(mixed $id): static {
        $model = static::find($id);
        
        if (!$model) {
            throw new \RuntimeException("Model not found with ID: {$id}");
        }
        
        return $model;
    }
    
    /**
     * Find record or create new
     */
    public static function findOrNew(mixed $id): static {
        $model = static::find($id);
        
        if ($model) {
            return $model;
        }
        
        return new static([static::$primaryKey => $id]);
    }
    /**
     * Query scope
     */
    public static function query(): QueryBuilder {
        $instance = new static();
        return $instance->newQuery();
    }
    
    /**
     * Create new query builder
     */
    public function newQuery(): QueryBuilder {
        $builder = new QueryBuilder($this->connection);
        $builder->table($this->getTable());
        
        return $builder;
    }
    
    /**
     * Save model to database
     */
    public function save(): bool {
        if ($this->exists) {
            return $this->update();
        }
        
        return $this->insert();
    }
    
    /**
     * Insert new record
     */
    protected function insert(): bool {
        $builder = $this->newQuery();
        
        $result = $builder->insert($this->attributes);
        
        if ($result) {
            $this->exists = true;
            $this->original = $this->attributes;
            
            // Set auto-increment ID if available
            $lastId = $this->connection->getPdo()->lastInsertId();
            if ($lastId) {
                $this->attributes[static::$primaryKey] = $lastId;
            }
        }
        
        return $result;
    }
    
    /**
     * Update existing record
     */
    protected function update(): bool {
        if (!$this->exists) {
            return false;
        }
        
        $builder = $this->newQuery();
        $id = $this->attributes[static::$primaryKey] ?? null;
        
        if (!$id) {
            return false;
        }
        
        $dirty = $this->getDirty();
        
        if (empty($dirty)) {
            return true;
        }
        
        $result = $builder->where(static::$primaryKey, $id)->update($dirty);
        
        if ($result) {
            $this->original = $this->attributes;
        }
        
        return $result;
    }
    
    /**
     * Get changed attributes
     */
    public function getDirty(): array {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }
    
    /**
     * Delete record
     */
    public function delete(): bool {
        if (!$this->exists) {
            return false;
        }
        
        $builder = $this->newQuery();
        $id = $this->attributes[static::$primaryKey] ?? null;
        
        if (!$id) {
            return false;
        }
        
        $result = $builder->where(static::$primaryKey, $id)->delete();
        
        if ($result) {
            $this->exists = false;
        }
        
        return $result;
    }
    
    /**
     * Refresh model from database
     */
    public function refresh(): bool {
        if (!$this->exists) {
            return false;
        }
        
        $id = $this->attributes[static::$primaryKey] ?? null;
        
        $model = static::find($id);
        
        if ($model) {
            $this->attributes = $model->attributes;
            $this->original = $model->original;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get attribute
     */
    public function getAttribute(string $key): mixed {
        if (array_key_exists($key, $this->attributes)) {
            return $this->castAttribute($key, $this->attributes[$key]);
        }
        
        return null;
    }
    
    /**
     * Set attribute
     */
    public function setAttribute(string $key, mixed $value): void {
        $this->attributes[$key] = $value;
    }
    
    /**
     * Cast attribute to native PHP type
     */
    protected function castAttribute(string $key, mixed $value): mixed {
        if ($value === null) {
            return null;
        }
        
        if (isset(static::$casts[$key])) {
            switch (static::$casts[$key]) {
                case 'int':
                case 'integer':
                    return (int) $value;
                case 'real':
                case 'float':
                case 'double':
                    return (float) $value;
                case 'string':
                    return (string) $value;
                case 'bool':
                case 'boolean':
                    return (bool) $value;
                case 'array':
                    return json_decode($value, true) ?? [];
                case 'json':
                    return json_decode($value, true) ?? [];
                case 'object':
                    return json_decode($value) ?? new \stdClass();
                case 'collection':
                    $decoded = json_decode($value, true) ?? [];
                    return is_array($decoded) ? $decoded : [];
                case 'date':
                case 'datetime':
                    return $this->asDateTime($value);
                case 'timestamp':
                    return $this->asDateTime($value);
            }
        }
        
        return $value;
    }
    
    /**
     * Convert string to DateTime
     */
    protected function asDateTime(string $value): \DateTime {
        try {
            return new \DateTime($value);
        } catch (\Exception $e) {
            return new \DateTime();
        }
    }
    
    /**
     * Convert model to array
     */
    public function toArray(): array {
        $array = $this->attributes;
        
        foreach ($array as $key => $value) {
            if (in_array($key, static::$hidden)) {
                unset($array[$key]);
            }
        }
        
        foreach ($this->relations as $key => $value) {
            $array[$key] = $value;
        }
        
        return $array;
    }
    
    /**
     * Convert model to JSON
     */
    public function toJson(int $options = 0): string {
        return json_encode($this->toArray(), $options);
    }
    
    /**
     * Magic method: get attribute
     */
    public function __get(string $key): mixed {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic method: set attribute
     */
    public function __set(string $key, mixed $value): void {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Magic method: check if attribute exists
     */
    public function __isset(string $key): bool {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Dynamic method calls
     */
    public function __call(string $method, array $parameters): mixed {
        if ($method === 'newQuery') {
            return $this->newQuery();
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
    
    /**
     * Static dynamic method calls
     */
    public static function __callStatic(string $method, array $parameters): mixed {
        $instance = new static();
        
        if (method_exists($instance, $method)) {
            return $instance->{$method}(...$parameters);
        }
        
        $query = $instance->newQuery();
        
        if (method_exists($query, $method)) {
            $result = $query->{$method}(...$parameters);
            
            if ($result instanceof QueryBuilder) {
                return $result;
            }
            
            return $result;
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
    
    /**
     * Create a new model instance
     */
    public static function create(array $attributes = []): static {
        $model = new static($attributes);
        $model->save();
        
        return $model;
    }
    
    /**
     * Update or create model
     */
    public static function updateOrCreate(array $attributes, array $values = []): static {
        $instance = new static();
        $builder = $instance->newQuery();
        
        $model = $builder->where($attributes)->first();
        
        if ($model) {
            $model->fill($values);
            $model->save();
            return $model;
        }
        
        return static::create(array_merge($attributes, $values));
    }
    
    /**
     * First or create
     */
    public static function firstOrCreate(array $attributes, array $values = []): static {
        $instance = new static();
        $builder = $instance->newQuery();
        
        $model = $builder->where($attributes)->first();
        
        if ($model) {
            return $model;
        }
        
        return static::create(array_merge($attributes, $values));
    }
}
