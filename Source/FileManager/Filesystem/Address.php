<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use Saeghe\Saeghe\DataType\Str;
use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Resolver;

trait Address
{
    private string $string;

    public function __construct(string $path_string)
    {
        $this->string = Resolver\realpath($path_string);
    }

    public function append(string $path_string): Path
    {
        return new Path($this->string . DIRECTORY_SEPARATOR . $path_string);
    }

    public function exists(): bool
    {
        return \file_exists($this->stringify());
    }

    public function leaf(): string
    {
        $leaf = Str\after_last_occurrence($this->string, DIRECTORY_SEPARATOR);

        return $leaf ?? $this->string;
    }

    public function parent(): Directory
    {
        return new Directory(Str\before_last_occurrence($this->string, DIRECTORY_SEPARATOR));
    }

    public function stringify(): string
    {
        return $this->string;
    }

    public function sibling(string $path): self
    {
        return new self($this->parent()->append($path)->stringify());
    }
}
