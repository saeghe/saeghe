<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use Saeghe\Saeghe\DataType\Str;
use Saeghe\Saeghe\FileManager\Path;

trait Address
{
    public function append(string $path_string): Path
    {
        return Path::from_string($this . DIRECTORY_SEPARATOR . $path_string);
    }

    public function exists(): bool
    {
        return \file_exists($this);
    }

    public function leaf(): string
    {
        if (strlen($this) === 1) {
            return $this;
        }

        $leaf = Str\after_last_occurrence($this, DIRECTORY_SEPARATOR);

        return $leaf ?? $this;
    }

    public function parent(): Directory
    {
        return Directory::from_string(Str\before_last_occurrence($this, DIRECTORY_SEPARATOR));
    }

    public function sibling(string $path): Path
    {
        return new Path($this->parent()->append($path)->string());
    }
}
