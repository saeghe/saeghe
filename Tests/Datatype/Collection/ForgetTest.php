<?php

namespace Tests\Datatype\Collection\ForgetTest;

use Saeghe\Saeghe\Datatype\Collection;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should forget items from the collection by given key',
    case: function () {
        $collection = new class([1 => 'foo', 2 => 'bar', 3 => 'baz']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };
        $collection->forget(2);

        assert_true([1 => 'foo', 3 => 'baz'] === $collection->items());
    }
);

test(
    title: 'it should do nothing when the key not exists',
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
        $collection->forget(3);

        assert_true([1 => 'foo', 2 => 'bar'] === $collection->items());
    }
);
