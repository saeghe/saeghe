<?php

namespace Tests\Datatype\Collection\CollectionTest;

use Saeghe\Saeghe\Datatype\Collection;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it can construct a collection without initial data',
    case: function () {
        $collection = new class() extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        assert_true($collection instanceof \ArrayAccess);
        assert_true([] === $collection->items());
    }
);

test(
    title: 'it can construct a collection with empty array',
    case: function () {
        $collection = new class([]) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        assert_true([] === $collection->items());
    }
);

test(
    title: 'it should validate key',
    case: function () {
        try {
            new class([1 => 'foo']) extends Collection {
                public function key_is_valid(mixed $key): bool
                {
                    return is_string($key);
                }

                public function value_is_valid(mixed $value): bool
                {
                    return true;
                }
            };
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
            new class([1 => 'foo']) extends Collection {
                public function key_is_valid(mixed $key): bool
                {
                    return true;
                }

                public function value_is_valid(mixed $value): bool
                {
                    return is_numeric($value);
                }
            };
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it should return count of the items',
    case: function () {
        $collection = new class(['foo', 'bar', 'baz', 'qux']) extends Collection {
            public function key_is_valid(mixed $key): bool
            {
                return true;
            }

            public function value_is_valid(mixed $value): bool
            {
                return true;
            }
        };

        assert_true($collection instanceof \Countable);
        assert_true(4 === count($collection));
    }
);
