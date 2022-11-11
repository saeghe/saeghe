<?php

namespace Saeghe\Saeghe\Config;

use Saeghe\Saeghe\DataType\Collection;
use Saeghe\Saeghe\Package;

class Packages extends Collection
{
    public function key_is_valid(mixed $key): bool
    {
        return is_string($key) && strlen($key) > 0;
    }

    public function value_is_valid(mixed $value): bool
    {
        return $value instanceof Package;
    }
}
