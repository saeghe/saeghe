<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use function Saeghe\Saeghe\FileManager\File\chmod;
use function Saeghe\Saeghe\FileManager\File\content;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;
use function Saeghe\Saeghe\FileManager\File\exists;
use function Saeghe\Saeghe\FileManager\File\lines;
use function Saeghe\Saeghe\FileManager\File\modify;
use function Saeghe\Saeghe\FileManager\File\permission;
use function Saeghe\Saeghe\FileManager\File\preserve_copy;

class File extends Filesystem
{
    public function chmod(int $permission): self
    {
        chmod($this->path, $permission);

        return $this;
    }

    public function content(): string
    {
        return content($this->path);
    }

    public function create(string $content, int $permission = 0664): self
    {
        create($this->path, $content, $permission);

        return $this;
    }

    public function delete(): self
    {
        delete($this->path);

        return $this;
    }

    public function exists(): bool
    {
        return exists($this->path);
    }

    public function lines(): \Generator
    {
        return lines($this->path);
    }

    public function modify(string $content): self
    {
        modify($this->path, $content);

        return $this;
    }

    public function permission(): int
    {
        return permission($this->path);
    }

    public function preserve_copy(File $destination): self
    {
        preserve_copy($this->path, $destination->path);

        return $this;
    }
}
