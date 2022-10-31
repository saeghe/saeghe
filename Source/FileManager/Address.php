<?php

namespace Saeghe\Saeghe\FileSystem;

use Saeghe\Saeghe\Str;
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

    public function parent(): static
    {
        return new static(Str\before_last_occurrence($this->string, DIRECTORY_SEPARATOR));
    }

    public function append(string $path_string): static
    {
        return static::from_string($this->string . DIRECTORY_SEPARATOR . $path_string);
    }

    public function directory(): string
    {
        if (Str\last_character($this->string) !== DIRECTORY_SEPARATOR) {
            return $this->string . DIRECTORY_SEPARATOR;
        }

        return $this->string;
    }
}
