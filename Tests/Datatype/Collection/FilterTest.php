<?php

namespace Tests\Datatype\Collection\FilterTest;

use Saeghe\Saeghe\Datatype\Collection;

test(
    title: 'it should filter items by given closure',
    case: function () {
        $collection = new class([1 => 'foo', 2 => 'bar', 3 => 'baz', 4 => 'qux']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        $result = $collection->filter(function ($value, $key) {
            return $key === 2 || $value === 'baz';
        });

        assert_true($result instanceof Collection);
        assert_true([2 => 'bar', 3 => 'baz'] === $result->items());
    }
);

test(
    title: 'it should filter empty values when closure not passed',
    case: function () {
        $collection = new class([1 => 'foo', 2 => '', 3 => null, 4 => 'qux', 5 => 0, null => 'value', '' => 'string']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        $result = $collection->filter();

        assert_true($result instanceof Collection);
        assert_true([1 => 'foo', 4 => 'qux', null => 'value', '' => 'string'] === $result->items());
    }
);
