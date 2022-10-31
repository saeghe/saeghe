<?php

namespace Saeghe\Saeghe\FileManager\Path;

use Saeghe\Saeghe\Str;

function realpath(string $pathString): string
{
    $pathString = rtrim(ltrim($pathString));
    $needle = DIRECTORY_SEPARATOR === '/' ? '\\' : '/';
    $pathString = str_replace($needle, DIRECTORY_SEPARATOR, $pathString);

    while (str_contains($pathString, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR)) {
        $pathString = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $pathString);
    }

    $pathString = str_replace(DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $pathString);
    $pathString = Str\last_character($pathString) === DIRECTORY_SEPARATOR ? Str\remove_last_character($pathString) : $pathString;

    $parts = explode(DIRECTORY_SEPARATOR, $pathString);

    while (in_array('..', $parts)) {
        foreach ($parts as $key => $part) {
            if ($part === '..') {
                unset($parts[$key - 1]);
                unset($parts[$key]);
                $parts = array_values($parts);
                break;
            }
        }
    }


    return implode(DIRECTORY_SEPARATOR, $parts);
}
