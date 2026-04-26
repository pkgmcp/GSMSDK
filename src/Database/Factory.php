<?php

declare(strict_types=1);

namespace GSMSDK\Database;

/**
 * Factory
 * 
 * Model factory for generating test data.
 */
abstract class Factory {
    protected int $count = 1;
    protected array $state = [];
    
    /**
     * Create new factory instance
     */
    public static function new(): static {
        return new static();
    }
    
    /**
     * Set number of records to create
     */
    public function count(int $count): static {
        $this->count = $count;
        return $this;
    }
    
    /**
     * Add state modifications
     */
    public function state(array $state): static {
        $this->state = array_merge($this->state, $state);
        return $this;
    }
    
    /**
     * Create and persist model instances
     */
    public function create(): array {
        $models = [];
        
        for ($i = 0; $i < $this->count; $i++) {
            $attributes = array_merge($this->definition(), $this->state);
            $model = $this->createModel($attributes);
            $models[] = $model;
        }
        
        return $models;
    }
    
    /**
     * Create model instance without persisting
     */
    public function make(): array {
        $models = [];
        
        for ($i = 0; $i < $this->count; $i++) {
            $attributes = array_merge($this->definition(), $this->state);
            $models[] = $this->makeModel($attributes);
        }
        
        return $this->count === 1 ? $models[0] : $models;
    }
    
    /**
     * Create and persist a single model
     */
    public function createOne(): mixed {
        $attributes = array_merge($this->definition(), $this->state);
        return $this->createModel($attributes);
    }
    /**
     * Create a model without persisting
     */
    public function makeOne(): mixed {
        $attributes = array_merge($this->definition(), $this->state);
        return $this->makeModel($attributes);
    }
    
    /**
     * Create model and persist to database
     */
    abstract protected function createModel(array $attributes): mixed;
    
    /**
     * Create model without persisting
     */
    abstract protected function makeModel(array $attributes): mixed;
    
    /**
     * Define default attributes
     */
    abstract protected function definition(): array;
}
