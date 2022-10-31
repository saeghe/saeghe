<?php

namespace Tests\StrTests\BeforeFirstOccurrenceTest;

use Saeghe\Saeghe\Str;

test(
    title: 'it should return empty string when needle is empty',
    case: function () {
        $subject = 'hello world';
        assert('' === Str\before_first_occurrence($subject, ''));
    }
);

test(
    title: 'it should return the substring before the first occurrence',
    case: function () {
        $subject = 'My\Class\Name';
        assert('My' === Str\before_first_occurrence($subject, '\\'));

        $subject = 'This is another sentence contains i to test';
        assert('This is another senten' === Str\before_first_occurrence($subject, 'c'));
    }
);

test(
    title: 'it should return empty string when needle is not in the subject',
    case: function () {
        $subject = 'hello world';
        assert('' === Str\before_first_occurrence($subject, 'my'));
    }
);
