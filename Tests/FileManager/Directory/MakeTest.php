<?php

namespace Tests\FileManager\Directory\makeTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should make a directory',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        assert_true(Directory\make($directory));
        assert_true(Directory\exists($directory));
        assert_true(0775 === Directory\permission($directory));

        return $directory;
    },
    after: function (Path $address) {
        Directory\delete($address);
    }
);

test(
    title: 'it should make a directory with the given permission',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        assert_true(Directory\make($directory, 0777));
        assert_true(Directory\exists($directory));
        assert_true(0777 === Directory\permission($directory));

        return $directory;
    },
    after: function (Path $address) {
        Directory\delete($address);
    }
);
