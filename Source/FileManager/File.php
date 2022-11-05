<?php

namespace Saeghe\Saeghe\FileManager\File;

function chmod(string $path, int $permission): bool
{
    $old_umask = umask(0);
    $return = \chmod($path, $permission);
    umask($old_umask);

    return $return;
}

function content(string $path): string
{
    return file_get_contents($path);
}

function copy(string $origin, string $destination): bool
{
    return \copy($origin, $destination);
}

function create(string $path, string $content, ?int $permission = 0664): bool
{
    $file = fopen($path, "w");
    fwrite($file, $content);
    $created = fclose($file);
    chmod($path, $permission);

    return $created;
}

function delete(string $path): bool
{
    return unlink($path);
}

function exists(string $path): bool
{
    return file_exists($path) && ! is_dir($path);
}

function modify(string $path, string $content): bool
{
    return false !== file_put_contents($path, $content);
}

function move(string $origin, string $destination): bool
{
    return rename($origin, $destination);
}

function permission(string $path): int
{
    clearstatcache();

    return fileperms($path) & 0x0FFF;
}

function preserve_copy(string $origin, string $destination): bool
{
    $copied = \copy($origin, $destination);
    chmod($destination, permission($origin));

    return $copied;
}
