<?php

namespace Saeghe\Saeghe\FileSystem;

use Saeghe\Saeghe\Str;
use Saeghe\Saeghe\FileManager\Path;

class Address
{
    private string $string;

    public function __construct(string $pathString)
    {
        $this->string = Path\realpath($pathString);
    }

    public static function fromString(string $pathString): static
    {
        return new static($pathString);
    }

    public function toString(): string
    {
        return $this->string;
    }

    public function parent(): static
    {
        return new static(Str\before_last_occurrence($this->string, DIRECTORY_SEPARATOR));
    }

    public function append(string $pathString): static
    {
        return static::fromString($this->string . DIRECTORY_SEPARATOR . $pathString);
    }

    public function directory(): string
    {
        if (Str\last_character($this->string) !== DIRECTORY_SEPARATOR) {
            return $this->string . DIRECTORY_SEPARATOR;
        }

        return $this->string;
    }
}
