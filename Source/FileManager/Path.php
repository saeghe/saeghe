<?php

namespace Saeghe\Saeghe\FileManager\Path;

use Saeghe\Saeghe\Str;

function realpath(string $path_string): string
{
    $path_string = rtrim(ltrim($path_string));
    $needle = DIRECTORY_SEPARATOR === '/' ? '\\' : '/';
    $path_string = str_replace($needle, DIRECTORY_SEPARATOR, $path_string);

    while (str_contains($path_string, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR)) {
        $path_string = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path_string);
    }

    $path_string = str_replace(DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path_string);
    $path_string = Str\last_character($path_string) === DIRECTORY_SEPARATOR ? Str\remove_last_character($path_string) : $path_string;

    $parts = explode(DIRECTORY_SEPARATOR, $path_string);

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
