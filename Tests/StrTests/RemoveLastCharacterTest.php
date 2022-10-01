<?php

namespace Tests\StrTests\RemoveLastCharacterTest;

require_once __DIR__ . '/../../Source/Str.php';

use Saeghe\Saeghe\Str;

test(
    title: 'it should remove last character',
    case: function () {
        $subject = 'hello world';
        assert('hello worl' === Str\remove_last_character($subject));
    }
);

test(
    title: 'it should return empty string when subject is empty',
    case: function () {
        $subject = '';
        assert('' === Str\remove_last_character($subject));
    }
);
