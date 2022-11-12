<?php

namespace Tests\Datatype\Str\StartsWithRegexTest;

use function Saeghe\Saeghe\Datatype\Str\starts_with_regex;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should check if string starts with given regex',
    case: function () {
        $valid = 'h3ll0 world';
        $invalid1 = 'word h3ll0';
        $invalid2 = 'hello world';
        $pattern = '[A-Za-z]\d[A-Za-z]{2}\d';

        assert_true(starts_with_regex($valid, $pattern));
        assert_false(starts_with_regex($invalid1, $pattern));
        assert_false(starts_with_regex($invalid2, $pattern));
    }
);

test(
    title: 'it should add extra backslashes when regex finish with backslash',
    case: function () {
        $valid1 = 'c:\\';
        $valid2 = 'c:\\filename';
        $invalid1 = ':\\';
        $invalid2 = '/root';
        $pattern = '[A-Za-z]:\\';

        assert_true(starts_with_regex($valid1, $pattern));
        assert_true(starts_with_regex($valid2, $pattern));
        assert_false(starts_with_regex($invalid1, $pattern));
        assert_false(starts_with_regex($invalid2, $pattern));
    }
);
