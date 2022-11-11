<?php

namespace Saeghe\Saeghe\Datatype;

class AnyText extends Text
{
    public function is_valid(string $string): bool
    {
        return true;
    }
}
