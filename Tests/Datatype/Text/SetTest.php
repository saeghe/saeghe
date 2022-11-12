<?php

namespace Tests\Datatype\Text\SetTest;

use Saeghe\Saeghe\Datatype\Text;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should set given text',
    case: function () {
        $text = new class('original') extends Text {
            public function is_valid(string $string): bool
            {
                return true;
            }
        };

        $text->set('modify');

        assert_true('modify' === $text->string());
    }
);

test(
    title: 'it should validate given text on set',
    case: function () {
        $text = new class('original') extends Text {
            public function is_valid(string $string): bool
            {
                return ! str_starts_with($string, 'm');
            }
        };

        try {
            $text->set('modify');
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid string passed to text.' === $exception->getMessage());
        }
    }
);
