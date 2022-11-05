<?php

namespace Saeghe\Saeghe\FileManager\FileType\Json;

function to_array(string $path): array
{
    return json_decode(json: file_get_contents($path), associative: true, flags: JSON_THROW_ON_ERROR);
}

function write(string $path, array $data): bool
{
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL) !== false;
}
