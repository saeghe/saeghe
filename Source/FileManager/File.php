<?php

namespace Saeghe\FileManager\File;

function delete(string $path): bool
{
    return unlink($path);
}

function move(string $origin, string $destination): bool
{
    return rename($origin, $destination);
}
