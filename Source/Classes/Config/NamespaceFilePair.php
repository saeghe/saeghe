<?php

namespace Saeghe\Saeghe\Classes\Config;

use Saeghe\Datatype\Pair;
use Saeghe\FileManager\Filesystem\Filename;

class NamespaceFilePair extends Pair
{
    public function namespace(): string
    {
        return $this->key;
    }

    public function filename(): Filename
    {
        return $this->value;
    }
}
