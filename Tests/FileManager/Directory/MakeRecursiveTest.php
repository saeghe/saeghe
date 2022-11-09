<?php

namespace Tests\FileManager\Directory\MakeRecursiveTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should create directory recursively',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Origin/MakeRecursive');

        assert_true(Directory\make_recursive($directory->stringify()));
        assert_true(Directory\exists($directory->parent()->stringify()));
        assert_true(Directory\exists($directory->stringify()));

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->parent()->stringify());
    }
);

test(
    title: 'it should create directory recursively with given permission',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Origin/MakeRecursive');

        assert_true(Directory\make_recursive($directory->stringify(), 0777));
        assert_true(Directory\exists($directory->parent()->stringify()));
        assert_true(0777 === Directory\permission($directory->parent()->stringify()));
        assert_true(Directory\exists($directory->stringify()));
        assert_true(0777 === Directory\permission($directory->stringify()));

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->parent()->stringify());
    }
);
