<?php

namespace Tests\StrTests\ReplaceFirstOccurrenceTest;

use Saeghe\Saeghe\Str;

test(
    title: 'it should replace first occurrence of sub string',
    case: function () {
        assert_true('hello universe' === Str\replace_first_occurrence('hello world', 'world', 'universe'));
        assert_true('hello universe world' === Str\replace_first_occurrence('hello world world', 'world', 'universe'));
        assert_true('hi world hello' === Str\replace_first_occurrence('hello world hello', 'hello', 'hi'));
        assert_true('' === Str\replace_first_occurrence('', 'hello', 'hi'));
    }
);
