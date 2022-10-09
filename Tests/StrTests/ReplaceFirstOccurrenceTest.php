<?php

namespace Tests\StrTests\ReplaceFirstOccurrenceTest;

require_once __DIR__ . '/../../Source/Str.php';

use Saeghe\Saeghe\Str;

test(
    title: 'it should replace first occurrence of sub string',
    case: function () {
        assert('hello universe' === Str\replace_first_occurrance('hello world', 'world', 'universe'));
        assert('hello universe world' === Str\replace_first_occurrance('hello world world', 'world', 'universe'));
        assert('hi world hello' === Str\replace_first_occurrance('hello world hello', 'hello', 'hi'));
        assert('' === Str\replace_first_occurrance('', 'hello', 'hi'));
    }
);
