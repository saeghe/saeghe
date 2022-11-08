<?php

namespace Saeghe\Saeghe\FileManager;

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

class DirectoryAddress extends Address
{
    public function chmod(int $permission): self
    {
        chmod($this->to_string(), $permission);

        return $this;
    }

    public function delete(): self
    {
        delete($this->to_string());

        return $this;
    }

    public function delete_recursive(): self
    {
        delete_recursive($this->to_string());

        return $this;
    }

    public function file(string $path): FileAddress
    {
        return new FileAddress($this->append($path)->to_string());
    }

    public function exists(): bool
    {
        return exists($this->to_string());
    }

    public function exists_or_create(): self
    {
        exists_or_create($this->to_string());

        return $this;
    }

    public function ls(): array
    {
        $list = ls($this->to_string());
        $results = [];

        foreach ($list as $item) {
            $item = $this->append($item)->to_string();

            if (is_dir($item)) {
                $results[] = DirectoryAddress::from_string($item);
            } else if (is_link($item)) {
                $results[] = SymlinkAddress::from_string($item);
            } else {
                $results[] = FileAddress::from_string($item);
            }
        }

        return $results;
    }

    public function ls_all(): array
    {
        $list = ls_all($this->to_string());
        $results = [];

        foreach ($list as $item) {
            $item = $this->append($item)->to_string();

            if (is_dir($item)) {
                $results[] = DirectoryAddress::from_string($item);
            } else if (is_link($item)) {
                $results[] = SymlinkAddress::from_string($item);
            } else {
                $results[] = FileAddress::from_string($item);
            }
        }

        return $results;
    }

    public function make(int $permission = 0775): self
    {
        make($this->to_string(), $permission);

        return $this;
    }

    public function make_recursive(int $permission = 0775): self
    {
        make_recursive($this->to_string(), $permission);

        return $this;
    }

    public function permission(): int
    {
        return permission($this->to_string());
    }

    public function preserve_copy(DirectoryAddress $destination): self
    {
        preserve_copy($this->to_string(), $destination->to_string());

        return $this;
    }

    public function renew(): self
    {
        renew($this->to_string());

        return $this;
    }

    public function renew_recursive(): self
    {
        renew_recursive($this->to_string());

        return $this;
    }

    public function subdirectory(string $path): static
    {
        return new static($this->append($path)->to_string());
    }

    public function symlink(string $path): SymlinkAddress
    {
        return new SymlinkAddress($this->append($path)->to_string());
    }
}
