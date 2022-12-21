<?php

namespace Saeghe\Saeghe\Classes\Credential;

use Saeghe\Datatype\Pair;

class Credential extends Pair
{
    public function provider(): string
    {
        return $this->key;
    }

    public function token(): string
    {
        return $this->value;
    }
}
