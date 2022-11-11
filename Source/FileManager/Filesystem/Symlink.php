<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use function Saeghe\Saeghe\FileManager\Symlink\delete;
use function Saeghe\Saeghe\FileManager\Symlink\exists;
use function Saeghe\Saeghe\FileManager\Symlink\link;

class Symlink extends Filesystem
{
    public function delete(): self
    {
        delete($this->path);

        return $this;
    }

    public function exists(): bool
    {
        return exists($this->path);
    }

    public function link(File $file): self
    {
        link($file->path, $this->path);

        return $this;
    }
}
