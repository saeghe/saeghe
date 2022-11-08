<?php

namespace Tests\FileManager\DirectoryAddress\MakeRecursiveTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should create directory recursively',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Origin/MakeRecursive');

        $result = $directory->make_recursive();
        assert_true($result->to_string() === $directory->to_string());
        assert_true($directory->parent()->exists());
        assert_true($directory->exists());

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->parent()->delete_recursive();
    }
);

test(
    title: 'it should create directory recursively with given permission',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Origin/MakeRecursive');

        $directory->make_recursive(0777);

        assert_true($directory->parent()->exists());
        assert_true(0777 === $directory->parent()->permission());
        assert_true($directory->exists());
        assert_true(0777 === $directory->permission());

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->parent()->delete_recursive();
    }
);
