<?php

namespace Tests\Str\Tests\LastCharacterTest;

use Saeghe\Saeghe\Str;

test(
    title: 'it should return last character',
    case: function () {
        assert('d' === Str\last_character('Hello World'), 'Last character is not what we want');
        assert('!' === Str\last_character('Hello World!'), 'Last character is not what we want');
        assert('' === Str\last_character(''), 'Last character is not what we want');
    }
);
