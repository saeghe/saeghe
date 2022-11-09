<?php

namespace Saeghe\Saeghe\FileManager;

use Saeghe\Saeghe\FileManager\Symlink;

class SymlinkAddress extends Address
{
    public function delete(): self
    {
        Symlink\delete($this->to_string());

        return $this;
    }

    public function exists(): bool
    {
        return Symlink\exists($this->to_string());
    }

    public function link(FileAddress $file): self
    {
        Symlink\link($file->to_string(), $this->to_string());

        return $this;
    }
}
