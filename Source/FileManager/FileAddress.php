<?php

namespace Saeghe\Saeghe\FileManager;

use Saeghe\Saeghe\FileManager\File;

class FileAddress extends Address
{
    public function chmod(int $permission): self
    {
        File\chmod($this->to_string(), $permission);

        return $this;
    }

    public function content(): string
    {
        return File\content($this->to_string());
    }

    public function create(string $content, int $permission = 0664): self
    {
        File\create($this->to_string(), $content, $permission);

        return $this;
    }

    public function delete(): self
    {
        File\delete($this->to_string());

        return $this;
    }

    public function exists(): bool
    {
        return File\exists($this->to_string());
    }

    public function lines(): \Generator
    {
        return File\lines($this->to_string());
    }

    public function modify(string $content): self
    {
        File\modify($this->to_string(), $content);

        return $this;
    }

    public function permission(): int
    {
        return File\permission($this->to_string());
    }

    public function preserve_copy(FileAddress $destination): self
    {
        File\preserve_copy($this->to_string(), $destination->to_string());

        return $this;
    }
}
