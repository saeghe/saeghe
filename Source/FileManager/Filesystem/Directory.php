<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\chmod;
use function Saeghe\Saeghe\FileManager\Directory\delete;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Directory\exists;
use function Saeghe\Saeghe\FileManager\Directory\exists_or_create;
use function Saeghe\Saeghe\FileManager\Directory\ls;
use function Saeghe\Saeghe\FileManager\Directory\ls_all;
use function Saeghe\Saeghe\FileManager\Directory\make;
use function Saeghe\Saeghe\FileManager\Directory\make_recursive;
use function Saeghe\Saeghe\FileManager\Directory\permission;
use function Saeghe\Saeghe\FileManager\Directory\preserve_copy;
use function Saeghe\Saeghe\FileManager\Directory\renew;
use function Saeghe\Saeghe\FileManager\Directory\renew_recursive;

class Directory
{
    use Address;

    public function chmod(int $permission): self
    {
        chmod($this->stringify(), $permission);

        return $this;
    }

    public function delete(): self
    {
        delete($this->stringify());

        return $this;
    }

    public function delete_recursive(): self
    {
        delete_recursive($this->stringify());

        return $this;
    }

    public function exists(): bool
    {
        return exists($this->stringify());
    }

    public function exists_or_create(): self
    {
        exists_or_create($this->stringify());

        return $this;
    }

    public function file(string $path): File
    {
        return new File($this->append($path)->stringify());
    }

    public function item(string $path): Directory|File|Symlink
    {
        $path = (new Path($this->stringify()))->append($path);

        if (is_dir($path->stringify())) {
            return $path->as_directory();
        }
        if (is_link($path->stringify())) {
            return $path->as_symlink();
        }

        return $path->as_file();
    }

    public function ls(): FilesystemCollection
    {
        $result = new FilesystemCollection();

        foreach (ls($this->stringify()) as $item) {
            $result->put($this->item($item));
        }

        return $result;
    }

    public function ls_all(): FilesystemCollection
    {
        $result = new FilesystemCollection();

        foreach (ls_all($this->stringify()) as $item) {
            $result->put($this->item($item));
        }

        return $result;
    }

    public function make(int $permission = 0775): self
    {
        make($this->stringify(), $permission);

        return $this;
    }

    public function make_recursive(int $permission = 0775): self
    {
        make_recursive($this->stringify(), $permission);

        return $this;
    }

    public function permission(): int
    {
        return permission($this->stringify());
    }

    public function preserve_copy(Directory $destination): self
    {
        preserve_copy($this->stringify(), $destination->stringify());

        return $this;
    }

    public function renew(): self
    {
        renew($this->stringify());

        return $this;
    }

    public function renew_recursive(): self
    {
        renew_recursive($this->stringify());

        return $this;
    }

    public function subdirectory(string $path): static
    {
        return new static($this->append($path)->stringify());
    }

    public function symlink(string $path): Symlink
    {
        return new Symlink($this->append($path)->stringify());
    }
}
