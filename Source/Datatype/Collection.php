<?php

namespace Saeghe\Saeghe\Datatype;

abstract class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    private array $items;

    public function __construct(?array $init = [])
    {
        $this->items = [];

        foreach ($init as $key => $value) {
            $this->put($value, $key);
        }
    }

    abstract public function key_is_valid(mixed $key): bool;

    abstract public function value_is_valid(mixed $value): bool;

    public function append(array $items): static
    {
        foreach ($items as $key => $value) {
            if (isset($this->items[$key])) {
                $this->put($value);
            } else {
                $this->put($value, $key);
            }
        }

        return $this;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function each(\Closure $closure): static
    {
        foreach ($this->items as $key => $value) {
            $closure($value, $key);
        }

        return $this;
    }

    public function except(?\Closure $closure = null): static
    {
        $closure = $closure ?? function ($value) {
            return (bool) $value;
        };

        $results = [];

        $this->each(function ($value, $key) use (&$results, $closure) {
            if (! $closure($value, $key)) {
                $results[$key] = $value;
            }
        });

        return new static($results);
    }

    public function filter(?\Closure $closure = null): static
    {
        if ($closure) {
            $results = [];

            $this->each(function ($value, $key) use (&$results, $closure) {
                if ($closure($value, $key)) {
                    $results[$key] = $value;
                }
            });

            return new static($results);
        }

        return new static(array_filter($this->items));
    }

    public function forget(mixed $key): static
    {
        unset($this->items[$key]);

        return $this;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function items(): array
    {
        return $this->items;
    }

    public function keys(): array
    {
        return array_keys($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->put($value, $offset);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function put(mixed $value, mixed $key = null): static
    {
        $this->validate_key($key);
        $this->validate_value($value);

        is_null($key) ? $this->items[] = $value : $this->items[$key] = $value;

        return $this;
    }

    public function reduce(\Closure $closure, mixed $carry = null): mixed
    {
        $result = $carry;

        foreach ($this->items() as $key => $value) {
            $result = $closure($result, $value, $key);
        }

        return $result;
    }

    private function validate_key(mixed $key): void
    {
        if (! $this->key_is_valid($key)) {
            throw new \InvalidArgumentException('Invalid key type passed to collection.');
        }
    }

    private function validate_value(mixed $value): void
    {
        if (! $this->value_is_valid($value)) {
            throw new \InvalidArgumentException('Invalid value type passed to collection.');
        }
    }

    public function values(): array
    {
        return array_values($this->items);
    }
}
