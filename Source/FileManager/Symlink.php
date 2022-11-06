<?php

namespace Saeghe\Saeghe\FileManager\Symlink;

function exists(string $path): bool
{
    return is_link($path);
}

function link(string $source_path, string $link_path): bool
{
    return symlink($source_path, $link_path);
}

function target(string $path): string
{
    return readlink($path);
}

function delete(string $path): bool
{
    return \unlink($path);
}
