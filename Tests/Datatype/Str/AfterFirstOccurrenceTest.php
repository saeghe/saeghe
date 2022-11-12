<?php

namespace Tests\Datatype\Str\AfterFirstOccurrenceTest;

use Saeghe\Saeghe\Datatype\Str;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return empty string when needle is empty',
    case: function () {
        $subject = 'hello world';
        assert_true('hello world' === Str\after_first_occurrence($subject, ''));
    }
);

test(
    title: 'it should return the substring after the first occurrence',
    case: function () {
        $subject = 'My\Class\Name';
        assert_true('Class\Name' === Str\after_first_occurrence($subject, '\\'));

        $subject = 'This is another sentence contains i to test';
        assert_true('e contains i to test' === Str\after_first_occurrence($subject, 'c'));
    }
);

test(
    title: 'it should return empty string when needle is not in the subject',
    case: function () {
        $subject = 'hello world';
        assert_true('' === Str\after_first_occurrence($subject, 'my'));
    }
);
