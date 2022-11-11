<?php

namespace Tests\DataType\Text\AnyTextTest;

use Saeghe\Saeghe\DataType\AnyText;

test(
    title: 'it can construct a text without initial data',
    case: function () {
        $text = new AnyText();

        assert_true('' === $text->string());
    }
);

test(
    title: 'it can construct a text with empty string',
    case: function () {
        $text = new AnyText('');

        assert_true('' === $text->string());
    }
);

test(
    title: 'it can accept any text',
    case: function () {
        $text = new AnyText(file_get_contents(__FILE__));

        assert_true(file_get_contents(__FILE__) === $text->string());
    }
);
