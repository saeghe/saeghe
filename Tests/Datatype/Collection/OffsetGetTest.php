<?php

namespace Tests\Datatype\Collection\OffsetGetTest;

use Saeghe\Saeghe\Datatype\Collection;

test(
    title: 'it should check should implement offsetGet',
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

        assert_true('foo' === $collection->offsetGet(1));
        assert_true('bar' === $collection->offsetGet(2));
    }
);
