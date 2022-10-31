<?php

namespace Tests\StrTests\AfterLastOccurrenceTest;

use Saeghe\Saeghe\Str;

test(
    title: 'it should return empty string when needle is empty',
    case: function () {
        $subject = 'hello world';
        assert('' === Str\after_last_occurrence($subject, ''));
    }
);

test(
    title: 'it should return the substring after the last occurrence',
    case: function () {
        $subject = 'My\Class\Name';
        assert('Name' === Str\after_last_occurrence($subject, '\\'));

        $subject = 'This is another sentence contains i to test';
        assert(' to test' === Str\after_last_occurrence($subject, 'i'));
    }
);

test(
    title: 'it should return empty string when needle is not in the subject',
    case: function () {
        $subject = 'hello world';
        assert('' === Str\after_last_occurrence($subject, 'my'));
    }
);
