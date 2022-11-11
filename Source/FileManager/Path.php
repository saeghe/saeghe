<?php

namespace Saeghe\Saeghe\FileManager;

use Saeghe\Saeghe\DataType\Text;
use Saeghe\Saeghe\FileManager\Filesystem\Address;
use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;
use function Saeghe\Saeghe\DataType\Str\starts_with_regex;

class Path extends Text
{
    use Address;

    public static function from_string(string $path_string): static
    {
        return new static(Resolver\realpath($path_string));
    }

    public function as_file(): File
    {
        return new File($this);
    }

    public function as_directory(): Directory
    {
        return new Directory($this);
    }

    public function as_symlink(): Symlink
    {
        return new Symlink($this);
    }

    public function is_valid(string $string): bool
    {
        return strlen($string) > 0 && (str_starts_with($string, '/') || starts_with_regex($string, '[A-Za-z]:\\'));
    }
}
