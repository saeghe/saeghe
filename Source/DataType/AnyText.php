<?php

namespace Saeghe\Saeghe\DataType;

class AnyText extends Text
{
    public function is_valid(string $string): bool
    {
        return true;
    }
}
