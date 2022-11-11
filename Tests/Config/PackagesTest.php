<?php

namespace Tests\Config\PackagesTest;

use Saeghe\Saeghe\Config\Packages;
use Saeghe\Saeghe\Package;

test(
    title: 'it should validate key',
    case: function () {
        try {
            new Packages([1 => 'foo']);
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
            new Packages(['key' => 1]);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid value type passed to collection.' === $exception->getMessage());
        }
    }
);

test(
    title: 'it accepts package as value',
    case: function () {
        $collection = new Packages(['key' => new Package('saeghe', 'cli')]);

        assert_true($collection->items()['key']->owner === 'saeghe');
    }
);

test(
    title: 'it accepts does not accepts empty string as packages key',
    case: function () {
        try {
            new Packages(['' => 'filename']);
            assert_true(false, 'code should not reach to this point');
        } catch (\Exception $exception) {
            assert_true($exception instanceof \InvalidArgumentException);
            assert_true('Invalid key type passed to collection.' === $exception->getMessage());
        }
    }
);
