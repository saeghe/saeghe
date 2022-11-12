<?php

namespace Tests\Datatype\Collection\AppendTest;

use Saeghe\Saeghe\Datatype\Collection;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should append items to collection items',
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

        assert_true([1 => 'foo', 2 => 'bar', 3 => 'baz', 4 => 'qux'] === $collection->append([3 => 'baz', 4 => 'qux'])->items());
    }
);

test(
    title: 'it should not override existed keys',
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


        assert_true([1 => 'foo', 2 => 'bar', 3 => 'baz', 4 => 'qux'] === $collection->append([1 => 'baz', 2 => 'qux'])->items());
    }
);
