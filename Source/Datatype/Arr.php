<?php

namespace Saeghe\Saeghe\Datatype\Arr;

function insert_after(array $array, mixed $key, array $additional): array
{
    $keys = array_keys($array);
    $index = array_search($key, $keys);
    $pos = false === $index ? count($array) : $index + 1;

    return array_merge(array_slice($array, 0, $pos), $additional, array_slice($array, $pos));
}
