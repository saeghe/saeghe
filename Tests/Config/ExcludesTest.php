<?php

namespace Tests\Config\ExcludesTest;

use Saeghe\Saeghe\Config\Excludes;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should validate key',
    case: function () {
        try {
            new Excludes(['excludes' => 'foo']);
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
            new Excludes([null]);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it accepts does not accepts empty string as excludes',
    case: function () {
        try {
            new Excludes(['']);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);
