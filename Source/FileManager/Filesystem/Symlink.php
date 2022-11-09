<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use function Saeghe\Saeghe\FileManager\Symlink\delete;
use function Saeghe\Saeghe\FileManager\Symlink\exists;
use function Saeghe\Saeghe\FileManager\Symlink\link;

class Symlink
{
    use Address;

    public function delete(): self
    {
        delete($this->stringify());

        return $this;
    }

    public function exists(): bool
    {
        return exists($this->stringify());
    }

    public function link(File $file): self
    {
        link($file->stringify(), $this->stringify());

        return $this;
    }
}
