<?php

namespace Tests\Datatype\Collection\PutTest;

use Saeghe\Saeghe\Datatype\Collection;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should put items to the collection',
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
        $collection->put('baz', 3);

        assert_true([1 => 'foo', 2 => 'bar', 3 => 'baz'] === $collection->items());
    }
);

test(
    title: 'it should put items to the collection by natural key when it is not passed',
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
        $collection->put('baz');

        assert_true([1 => 'foo', 2 => 'bar', 'baz'] === $collection->items());
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
            $collection->put('foo', 1);
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
            $collection->put('foo', 1);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);
