<?php

namespace Tests\FileManager\Directory\makeTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should make a directory',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        assert_true(Directory\make($directory->stringify()));
        assert_true(Directory\exists($directory->stringify()));
        assert_true(0775 === Directory\permission($directory->stringify()));

        return $directory;
    },
    after: function (Path $address) {
        Directory\delete($address->stringify());
    }
);

test(
    title: 'it should make a directory with the given permission',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        assert_true(Directory\make($directory->stringify(), 0777));
        assert_true(Directory\exists($directory->stringify()));
        assert_true(0777 === Directory\permission($directory->stringify()));

        return $directory;
    },
    after: function (Path $address) {
        Directory\delete($address->stringify());
    }
);
