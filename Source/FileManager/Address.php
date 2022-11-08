<?php

namespace Saeghe\Saeghe\FileManager;

use Saeghe\Saeghe\DataType\Str;
use Saeghe\Saeghe\FileManager\Path;

class Address
{
    private string $string;

    public function __construct(string $path_string)
    {
        $this->string = Path\realpath($path_string);
    }

    public static function from_string(string $path_string): static
    {
        return new static($path_string);
    }

    public function to_string(): string
    {
        return $this->string;
    }

    public function parent(): DirectoryAddress
    {
        return new DirectoryAddress(Str\before_last_occurrence($this->string, DIRECTORY_SEPARATOR));
    }

    public function leaf(): string
    {
        $leaf = Str\after_last_occurrence($this->string, DIRECTORY_SEPARATOR);

        return $leaf ?? $this->string;
    }

    public function append(string $path_string): static
    {
        return static::from_string($this->string . DIRECTORY_SEPARATOR . $path_string);
    }

    public function exists(): bool
    {
        return \file_exists($this->to_string());
    }
}
