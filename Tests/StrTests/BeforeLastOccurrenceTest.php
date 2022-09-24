<?php

namespace Tests\StrTests\BeforeLastOccurrenceTest;

require_once __DIR__ . '/../../Source/Str.php';

use Saeghe\Saeghe\Str;

test(
    title: 'it should return the subject when needle is empty',
    case: function () {
        $subject = 'hello world';
        assert($subject === Str\before_last_occurrence($subject, ''));
    }
);

test(
    title: 'it should return the substring before the last occurrence',
    case: function () {
        $subject = 'My\Class\Name';
        assert('My\Class' === Str\before_last_occurrence($subject, '\\'));

        $subject = 'This is another sentence contains i to test';
        assert('This is another sentence contains ' === Str\before_last_occurrence($subject, 'i'));
    }
);

test(
    title: 'it should return subject string when needle is not in the subject',
    case: function () {
        $subject = 'hello world';
        assert('hello world' === Str\before_last_occurrence($subject, 'my'));
    }
);
