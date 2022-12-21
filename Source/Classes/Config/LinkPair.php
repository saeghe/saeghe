<?php

namespace Saeghe\Saeghe\Classes\Config;

use Saeghe\Datatype\Pair;
use Saeghe\FileManager\Filesystem\Filename;
class LinkPair extends Pair
{
    public function symlink(): Filename
    {
        return $this->key;
    }

    public function source(): Filename
    {
        return $this->value;
    }
}
