<?php

namespace Saeghe\Saeghe\Classes\Config;

use Saeghe\Datatype\Pair;
use Saeghe\FileManager\Path;

class NamespacePathPair extends Pair
{
    public function namespace(): string
    {
        return $this->key;
    }

    public function path(): Path
    {
        return $this->value;
    }
}
