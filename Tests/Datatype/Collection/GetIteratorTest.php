<?php

namespace Tests\Datatype\Collection\GetIteratorTest;

use Saeghe\Saeghe\Datatype\Collection;

test(
    title: 'it should implement getIterator',
    case: function () {
        $collection = new class(['foo' => 'bar', 'baz' => 'qux']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        $actual = [];
        foreach ($collection as $key => $value) {
            $actual[$key] = $value;
        }

        assert_true($actual === ['foo' => 'bar', 'baz' => 'qux']);
    }
);
