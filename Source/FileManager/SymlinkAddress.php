<?php

namespace Saeghe\Saeghe\FileManager;

use function Saeghe\Saeghe\FileManager\Symlink\delete;
use function Saeghe\Saeghe\FileManager\Symlink\exists;
use function Saeghe\Saeghe\FileManager\Symlink\link;

class SymlinkAddress extends Address
{
    public function delete(): self
    {
        delete($this->to_string());

        return $this;
    }

    public function exists(): bool
    {
        return exists($this->to_string());
    }

    public function link(FileAddress $file): self
    {
        link($file->to_string(), $this->to_string());

        return $this;
    }
}
