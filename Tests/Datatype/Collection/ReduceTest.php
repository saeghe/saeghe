<?php

namespace Tests\Datatype\Collection\ReduceTest;

use Saeghe\Saeghe\Datatype\Collection;

test(
    title: 'it should reduce collection',
    case: function () {
        $collection = new class([1 => 'foo', 2 => 'bar']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        $actual = $collection->reduce(function ($carry, $value) {
            return $value === 'bar' || $carry;
        }, false);

        assert_true($actual);
    }
);

test(
    title: 'it should reduce collection using key',
    case: function () {
        $collection = new class([1 => 'foo', 2 => 'bar']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        $actual = $collection->reduce(function ($carry, $value, $key) {
            return $key === 2 || $carry;
        }, false);

        assert_true($actual);
    }
);

test(
    title: 'it should set carry as null when not passed',
    case: function () {
        $collection = new class([1 => 'foo', 2 => 'bar']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        $actual = $collection->reduce(function ($carry) {
            return $carry;
        });

        assert_true(is_null($actual));
    }
);
