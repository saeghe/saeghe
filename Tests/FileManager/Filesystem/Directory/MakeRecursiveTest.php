<?php

namespace Tests\FileManager\Filesystem\Directory\MakeRecursiveTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should create directory recursively',
    case: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/Origin/MakeRecursive');

        $result = $directory->make_recursive();
        assert_true($result->stringify() === $directory->stringify());
        assert_true($directory->parent()->exists());
        assert_true($directory->exists());

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->parent()->delete_recursive();
    }
);

test(
    title: 'it should create directory recursively with given permission',
    case: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/Origin/MakeRecursive');

        $directory->make_recursive(0777);

        assert_true($directory->parent()->exists());
        assert_true(0777 === $directory->parent()->permission());
        assert_true($directory->exists());
        assert_true(0777 === $directory->permission());

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->parent()->delete_recursive();
    }
);
