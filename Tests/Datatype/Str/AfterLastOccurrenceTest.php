<?php

namespace Tests\Datatype\Str\AfterLastOccurrenceTest;

use Saeghe\Saeghe\Datatype\Str;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return empty string when needle is empty',
    case: function () {
        $subject = 'hello world';
        assert_true('' === Str\after_last_occurrence($subject, ''));
    }
);

test(
    title: 'it should return the substring after the last occurrence',
    case: function () {
        $subject = 'My\Class\Name';
        assert_true('Name' === Str\after_last_occurrence($subject, '\\'));

        $subject = 'This is another sentence contains i to test';
        assert_true(' to test' === Str\after_last_occurrence($subject, 'i'));
    }
);

test(
    title: 'it should return empty string when needle is not in the subject',
    case: function () {
        $subject = 'hello world';
        assert_true('' === Str\after_last_occurrence($subject, 'my'));
    }
);
