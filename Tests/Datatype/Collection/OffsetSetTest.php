<?php

namespace Tests\Datatype\Collection\OffsetSetTest;

use Saeghe\Saeghe\Datatype\Collection;

test(
    title: 'it should check should implement offsetSet',
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

        $collection->offsetSet(3, 'baz');

        assert_true([1 => 'foo', 2 => 'bar', 3 => 'baz'] === $collection->items());
    }
);


test(
    title: 'it should validate key',
    case: function () {
        try {
            $collection = new class() extends Collection {
                public function key_is_valid(mixed $key): bool
                {
                    return is_string($key);
                }

                public function value_is_valid(mixed $value): bool
                {
                    return true;
                }
            };
            $collection->offsetSet(1, 'foo');
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid key type passed to collection.' === $exception->getMessage());
        }
    }
);


test(
    title: 'it should validate value',
    case: function () {
        try {
            $collection = new class([1 => 'foo']) extends Collection {
                public function key_is_valid(mixed $key): bool
                {
                    return true;
                }

                public function value_is_valid(mixed $value): bool
                {
                    return is_numeric($value);
                }
            };
            $collection->offsetSet(1, 'foo');
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

