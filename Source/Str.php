<?php

namespace Saeghe\Saeghe\Str;

function between($string, $start, $end)
{
    $startPosition = stripos($string, $start);
    $first = substr($string, $startPosition);
    $second = substr($first, strlen($start));
    $positionEnd = stripos($second, $end);
    $final = substr($second, 0, $positionEnd);

    return trim($final);
}

function after_last_occurrence($subject, $needle)
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

function before_first_occurrence($subject, $needle)
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

function before_last_occurrence($subject, $needle)
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

function last_character($subject)
{
    return mb_substr($subject, -1);
}

function remove_last_character($subject)
{
    return substr_replace($subject ,"",-1);
}

function replace_first_occurrance($subject, $search, $replace)
{
    return preg_replace('/' . $search . '/', $replace, $subject, 1);
}
