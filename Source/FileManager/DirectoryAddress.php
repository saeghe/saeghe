<?php

namespace Saeghe\Saeghe\FileManager;

use Saeghe\Saeghe\FileManager\Directory;

class DirectoryAddress extends Address
{
    public function chmod(int $permission): self
    {
        Directory\chmod($this->to_string(), $permission);

        return $this;
    }

    public function delete(): self
    {
        Directory\delete($this->to_string());

        return $this;
    }

    public function delete_recursive(): self
    {
        Directory\delete_recursive($this->to_string());

        return $this;
    }

    public function exists(): bool
    {
        return Directory\exists($this->to_string());
    }

    public function exists_or_create(): self
    {
        Directory\exists_or_create($this->to_string());

        return $this;
    }

    public function file(string $path): FileAddress
    {
        return new FileAddress($this->append($path)->to_string());
    }

    public function item(string $path): DirectoryAddress|FileAddress|SymlinkAddress|Address
    {
        $address = Address::from_string($this->to_string())->append($path);

        if (is_dir($address->to_string())) {
            return DirectoryAddress::from_string($address->to_string());
        }
        if (is_link($address->to_string())) {
            return SymlinkAddress::from_string($address->to_string());
        }
        if (\file_exists($address->to_string())) {
            return FileAddress::from_string($address->to_string());
        }

        return $address;
    }

    public function ls(): array
    {
        $list = Directory\ls($this->to_string());
        $results = [];

        foreach ($list as $item) {
            $results[] = $this->item($item);
        }

        return $results;
    }

    public function ls_all(): array
    {
        $list = Directory\ls_all($this->to_string());
        $results = [];

        foreach ($list as $item) {
            $results[] = $this->item($item);
        }

        return $results;
    }

    public function make(int $permission = 0775): self
    {
        Directory\make($this->to_string(), $permission);

        return $this;
    }

    public function make_recursive(int $permission = 0775): self
    {
        Directory\make_recursive($this->to_string(), $permission);

        return $this;
    }

    public function permission(): int
    {
        return Directory\permission($this->to_string());
    }

    public function preserve_copy(DirectoryAddress $destination): self
    {
        Directory\preserve_copy($this->to_string(), $destination->to_string());

        return $this;
    }

    public function renew(): self
    {
        Directory\renew($this->to_string());

        return $this;
    }

    public function renew_recursive(): self
    {
        Directory\renew_recursive($this->to_string());

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
