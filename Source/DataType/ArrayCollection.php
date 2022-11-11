<?php

namespace Saeghe\Saeghe\DataType;

class ArrayCollection extends Collection
{
    public function key_is_valid(mixed $key): bool
    {
        return true;
    }

    public function value_is_valid(mixed $value): bool
    {
        return true;
    }
}
