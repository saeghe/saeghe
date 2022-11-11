<?php

namespace Tests\DataType\Text\TextTest;

use Saeghe\Saeghe\DataType\Text;

test(
    title: 'it can construct a text without initial data',
    case: function () {
        $text = new class() extends Text {
            public function is_valid(string $string): bool
            {
                return true;
            }
        };

        assert_true('' === $text->string());
    }
);

test(
    title: 'it can construct a text with empty string',
    case: function () {
        $text = new class('') extends Text {
            public function is_valid(string $string): bool
            {
                return true;
            }
        };

        assert_true('' === $text->string());
    }
);


test(
    title: 'it can construct a text with stringable object',
    case: function () {
        $stringable = new class(['a' => 'b']) implements \Stringable {
            public function __construct(private array $arr) {}

            public function __toString(): string
            {
                return json_encode($this->arr);
            }
        };
        $text = new class($stringable) extends Text {
            public function is_valid(string $string): bool
            {
                return true;
            }
        };

        assert_true((string) $stringable === $text->string());
    }
);

test(
    title: 'it should validate text',
    case: function () {
        try {
            new class('foo') extends Text {
                public function is_valid(string $string): bool
                {
                    return strlen($string) > 5;
                }
            };
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid string passed to text.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it should implement stringable',
    case: function () {
        $text = new class('hello world') extends Text {
            public function is_valid(string $string): bool
            {
                return strlen($string) > 5;
            }
        };
        assert_true('hello world' === (string) $text);
    }
);
