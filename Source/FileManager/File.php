<?php

namespace Saeghe\Saeghe\FileManager\File;

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
