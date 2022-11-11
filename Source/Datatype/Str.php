<?php

namespace Saeghe\Saeghe\Datatype\Str;

function after_first_occurrence(string $subject, string $needle): string
{
    if ($needle === '') {
        return $subject;
    }

    $pos = mb_strpos($subject, $needle);

    if ($pos === false) {
        return '';
    }

    return mb_substr(string: $subject, start: $pos + 1,  encoding: 'UTF-8');
}

function after_last_occurrence(string $subject, string $needle): string
{
    if ($needle === '') {
        return '';
    }

    $pos = mb_strrpos($subject, $needle);

    if ($pos === false) {
        return '';
    }

    return mb_substr(string: $subject, start: $pos + 1,  encoding: 'UTF-8');
}

function before_first_occurrence(string $subject, string $needle): string
{
    if ($needle === '') {
        return '';
    }

    $pos = mb_strpos($subject, $needle);

    if ($pos === false) {
        return '';
    }

    return mb_substr(string: $subject, start: 0, length: $pos,  encoding: 'UTF-8');
}

function before_last_occurrence(string $subject, string $needle): string
{
    if ($needle === '') {
        return $subject;
    }

    $pos = mb_strrpos($subject, $needle);

    if ($pos === false) {
        return $subject;
    }

    return mb_substr(string: $subject, start: 0, length: $pos,  encoding: 'UTF-8');
}

function between(string $string, string $start, string $end): string
{
    $start_position = stripos($string, $start);
    $first = substr($string, $start_position);
    $second = substr($first, strlen($start));
    $position_end = stripos($second, $end);
    $final = substr($second, 0, $position_end);

    return trim($final);
}

function last_character(string $subject): string
{
    return mb_substr($subject, -1);
}

function remove_first_character(string $subject): array|string
{
    return substr_replace($subject ,"",0, 1);
}

function remove_last_character(string $subject): string
{
    return substr_replace($subject ,"",-1);
}

function replace_first_occurrence(string $subject, string $search, string $replace): string
{
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

function starts_with_regex(string $subject, string $pattern): bool
{
    $pattern = str_ends_with($pattern, '\\') ? $pattern . '\\' : $pattern;

    return preg_match("/^$pattern/", $subject);
}
