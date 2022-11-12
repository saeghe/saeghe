<?php

namespace Tests\Datatype\Str\LastCharacterTest;

use Saeghe\Saeghe\Datatype\Str;

test(
    title: 'it should return last character',
    case: function () {
        assert_true('d' === Str\last_character('Hello World'), 'Last character is not what we want');
        assert_true('!' === Str\last_character('Hello World!'), 'Last character is not what we want');
        assert_true('' === Str\last_character(''), 'Last character is not what we want');
    }
);
