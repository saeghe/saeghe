<?php

namespace Saeghe\Saeghe\FileManager;

use Saeghe\Saeghe\FileManager\Filesystem\Address;
use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

class Path
{
    use Address;

    public static function from_string(string $path_string): static
    {
        return new static($path_string);
    }

    public function as_file(): File
    {
        return new File($this->stringify());
    }

    public function as_directory(): Directory
    {
        return new Directory($this->stringify());
    }

    public function as_symlink(): Symlink
    {
        return new Symlink($this->stringify());
    }
}
