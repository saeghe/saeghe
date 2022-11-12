<?php

namespace Tests\Config\EntryPointsTest;

use Saeghe\Saeghe\Config\EntryPoints;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should validate key',
    case: function () {
        try {
            new EntryPoints(['entry-point' => 'foo']);
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
            new EntryPoints([null]);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it accepts does not accepts empty string as entry-point',
    case: function () {
        try {
            new EntryPoints(['']);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);
