<?php

namespace Tests\Config\MapTest;

use Saeghe\Saeghe\Config\Map;

test(
    title: 'it should validate key',
    case: function () {
        try {
            new Map([1 => 'foo']);
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
            new Map(['foo' => null]);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it accepts empty string as key or value',
    case: function () {
        $configMap = new Map(['' => '']);

        assert_true(['' => ''] === $configMap->items());
    }
);
