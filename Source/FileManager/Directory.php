<?php

namespace Saeghe\Saeghe\FileManager\Directory;

function delete_recursive(string $path): bool
{
    flush($path);

    return rmdir($path);
}

function exists(string $path): bool
{
    return file_exists($path) && is_dir($path);
}

function flush(string $path): void
{
    $dir = opendir($path);

    while (false !== ($file = readdir($dir))) {
        if (! in_array($file, ['.', '..'])) {
            $path_to_file = $path . DIRECTORY_SEPARATOR . $file;
            is_dir($path_to_file) ? delete_recursive($path_to_file) : unlink($path_to_file);
        }
    }

    closedir($dir);
}

function is_empty(string $path): bool
{
    return scandir($path) == ['.', '..'];
}
