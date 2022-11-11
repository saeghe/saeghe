<?php

namespace Tests\DataType\Collection\KeysTest;

use Saeghe\Saeghe\DataType\Collection;

test(
    title: 'it should run collection keys',
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

        assert_true([1, 2] === $collection->keys());
    }
);
