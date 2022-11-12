<?php

namespace Saeghe\Saeghe\FileManager\Filesystem;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Symlink\delete;
use function Saeghe\Saeghe\FileManager\Symlink\exists;
use function Saeghe\Saeghe\FileManager\Symlink\link;

class Symlink implements \Stringable
{
    use Address;

    public function __construct(public Path $path) {}

    public static function from_string(string $path_string): static
    {
        return new static(Path::from_string($path_string));
    }

    public function __toString(): string
    {
        return $this->path->string();
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

    public function link(File $file): self
    {
        link($file->path, $this->path);

        return $this;
    }
}
