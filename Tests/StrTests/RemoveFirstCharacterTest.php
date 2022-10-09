<?php

namespace Tests\StrTests\RemoveFirstCharacterTest;

require_once __DIR__ . '/../../Source/Str.php';

use Saeghe\Saeghe\Str;

test(
    title: 'it should remove first character',
    case: function () {
        $subject = 'hello world';
        assert('ello world' === Str\remove_first_character($subject));
    }
);

test(
    title: 'it should return empty string when subject is empty',
    case: function () {
        $subject = '';
        assert('' === Str\remove_first_character($subject));
    }
);
