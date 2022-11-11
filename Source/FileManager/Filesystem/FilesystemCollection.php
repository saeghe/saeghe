<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use Saeghe\Saeghe\Datatype\Collection;

class FilesystemCollection extends Collection
{
    public function key_is_valid(mixed $key): bool
    {
        return is_null($key) || is_integer($key);
    }

    public function value_is_valid(mixed $value): bool
    {
        return $value instanceof Directory || $value instanceof File || $value instanceof Symlink;
    }
}
