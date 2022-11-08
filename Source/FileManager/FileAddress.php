<?php

namespace Saeghe\Saeghe\FileManager;

use function Saeghe\Saeghe\FileManager\File\chmod;
use function Saeghe\Saeghe\FileManager\File\content;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;
use function Saeghe\Saeghe\FileManager\File\exists;
use function Saeghe\Saeghe\FileManager\File\lines;
use function Saeghe\Saeghe\FileManager\File\modify;
use function Saeghe\Saeghe\FileManager\File\permission;
use function Saeghe\Saeghe\FileManager\File\preserve_copy;

class FileAddress extends Address
{
    public function chmod(int $permission): self
    {
        chmod($this->to_string(), $permission);

        return $this;
    }

    public function content(): string
    {
        return content($this->to_string());
    }

    public function create(string $content, int $permission = 0664): self
    {
        create($this->to_string(), $content, $permission);

        return $this;
    }

    public function delete(): self
    {
        delete($this->to_string());

        return $this;
    }

    public function exists(): bool
    {
        return exists($this->to_string());
    }

    public function lines(): \Generator
    {
        return lines($this->to_string());
    }

    public function modify(string $content): self
    {
        modify($this->to_string(), $content);

        return $this;
    }

    public function permission(): int
    {
        return permission($this->to_string());
    }

    public function preserve_copy(FileAddress $destination): self
    {
        preserve_copy($this->to_string(), $destination->to_string());

        return $this;
    }
}
