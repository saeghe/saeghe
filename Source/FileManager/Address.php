<?php

namespace Saeghe\Saeghe\FileManager;

use Saeghe\Saeghe\DataType\Str;
use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;
use Saeghe\Saeghe\FileManager\Symlink;

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

    public function leaf(): string
    {
        $leaf = Str\after_last_occurrence($this->string, DIRECTORY_SEPARATOR);

        return $leaf ?? $this->string;
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

    public function exists(): bool
    {
        return File\exists($this->to_string());
    }

    public function is_directory(): bool
    {
        return Directory\exists($this->string);
    }

    public function is_file(): bool
    {
        return File\exists($this->string);
    }

    public function is_symlink(): bool
    {
        return Symlink\exists($this->string);
    }
}
