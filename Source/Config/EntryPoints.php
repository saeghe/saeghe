<?php

namespace Saeghe\Saeghe\Config;

use Saeghe\Saeghe\DataType\Collection;

class EntryPoints extends Collection
{
    public function key_is_valid(mixed $key): bool
    {
        return is_null($key) || is_integer($key);
    }

    public function value_is_valid(mixed $value): bool
    {
        return is_string($value) && strlen($value) > 0;
    }
}
