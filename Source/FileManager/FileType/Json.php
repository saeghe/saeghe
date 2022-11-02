<?php

namespace Saeghe\Saeghe\FileManager\FileType\Json;

function to_array(string $path): array
{
    return json_decode(json: file_get_contents($path), associative: true, flags: JSON_THROW_ON_ERROR);
}
