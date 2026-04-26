<?php

declare(strict_types=1);

namespace GSMSDK\Database;

/**
 * Fluent Query Builder
 */
class QueryBuilder
{
    private string $table;
    private array $columns = ['*'];
    private array $wheres = [];
    private ?string $orderBy = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private array $joins = [];
    private array $bindings = [];

    public function __construct(private Connection $connection)
    {
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array|string $columns = '*'): self
    {
        $this->columns = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    public function where(string $column, string $operator, mixed $value = null, string $boolean = 'AND'): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value', 'boolean');
        $this->bindings[] = $value;

        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'IN',
            'value' => "({$placeholders})",
            'boolean' => $boolean,
            'raw' => true,
        ];
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "{$column} " . (strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function get(): array
    {
        $sql = $this->toSql();
        return $this->connection->fetchAll($sql, $this->bindings);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $backupColumns = $this->columns;
        $this->columns = ['COUNT(*) as count'];
        $result = $this->first();
        $this->columns = $backupColumns;
        return (int) ($result['count'] ?? 0);
    }

    public function insert(array $values): string
    {
        return $this->connection->insert($this->table, $values);
    }

    public function update(array $values): int
    {
        return $this->connection->update($this->table, $values, $this->buildWhereConditions());
    }

    public function delete(): int
    {
        return $this->connection->delete($this->table, $this->buildWhereConditions());
    }

    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    private function buildWhereClause(): string
    {
        $clauses = [];
        foreach ($this->wheres as $index => $where) {
            $prefix = $index === 0 ? '' : $where['boolean'];
            if ($where['raw'] ?? false) {
                $clauses[] = "{$prefix} {$where['column']} {$where['operator']} {$where['value']}";
            } else {
                $clauses[] = "{$prefix} {$where['column']} {$where['operator']} ?";
            }
        }
        return implode(' ', $clauses);
    }

    private function buildWhereConditions(): array
    {
        $conditions = [];
        foreach ($this->wheres as $where) {
            $conditions[$where['column']] = $where['value'];
        }
        return $conditions;
    }
}
