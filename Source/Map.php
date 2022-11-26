<?php

namespace Saeghe\Saeghe;

use Saeghe\Datatype\Collection;
use Saeghe\FileManager\Path;
use function Saeghe\Datatype\Str\after_first_occurrence;

class Map extends Collection
{

    public function key_is_valid(mixed $key): bool
    {
        return is_string($key);
    }

    public function value_is_valid(mixed $value): bool
    {
        return $value instanceof Path;
    }

    public function find(string $import, bool $absolute): ?Path
    {
        if ($absolute) {
            $path = $this->items()[$import] ?? '';
            return str_ends_with($path, '.php') ? $path : null;
        }

        return $this->reduce(function (?Path $carry, Path $path, string $namespace) use ($import) {
            return str_starts_with($import, $namespace)
                ? $path->append(after_first_occurrence($import, $namespace) . '.php')
                : $carry;
        });
    }
}
