<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use Saeghe\Saeghe\Datatype\Str;
use Saeghe\Saeghe\FileManager\Path;

trait Address
{
    public function append(string $path): Path
    {
        return Path::from_string($this . DIRECTORY_SEPARATOR . $path);
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

    public function relocate(string $origin, string $destination): Path
    {
        $path = Str\replace_first_occurrence($this, $origin, $destination);

        return Path::from_string($path);
    }

    public function sibling(string $path): Path
    {
        return new Path($this->parent()->append($path)->string());
    }
}
