<?php

namespace Tests\Config\ExecutablesTest;

use Saeghe\Saeghe\Config\Executables;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should validate key',
    case: function () {
        try {
            new Executables([1 => 'foo']);
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
            new Executables(['key' => 1]);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it accepts does not accepts empty string as executables',
    case: function () {
        try {
            new Executables(['key' => '']);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it accepts does not accepts empty string as executables filename',
    case: function () {
        try {
            new Executables(['' => 'filename']);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid key type passed to collection.' === $exception->getMessage());
        }
    }
);
