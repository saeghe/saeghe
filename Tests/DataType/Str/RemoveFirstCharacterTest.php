<?php

namespace Tests\DataType\Str\RemoveFirstCharacterTest;

use Saeghe\Saeghe\DataType\Str;

test(
    title: 'it should remove first character',
    case: function () {
        $subject = 'hello world';
        assert_true('ello world' === Str\remove_first_character($subject));
    }
);

test(
    title: 'it should return empty string when subject is empty',
    case: function () {
        $subject = '';
        assert_true('' === Str\remove_first_character($subject));
    }
);