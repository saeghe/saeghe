<?php

namespace Saeghe\Saeghe\FileManager\File;

function content(string $path): string
{
    return file_get_contents($path);
}

function create(string $path, string $content): bool
{
    return false !== file_put_contents($path, $content);
}

function delete(string $path): bool
{
    return unlink($path);
}

function exists(string $path): bool
{
    return file_exists($path) && ! is_dir($path);
}

function move(string $origin, string $destination): bool
{
    return rename($origin, $destination);
}
