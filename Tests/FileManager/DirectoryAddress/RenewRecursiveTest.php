<?php

namespace Tests\FileManager\DirectoryAddress\RenewRecursiveTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should clean directory when directory exists',
    case: function (DirectoryAddress $directory) {
        $result = $directory->renew_recursive();

        assert_true($result->to_string() === $directory->to_string());
        assert_true($directory->exists());
        assert_false($directory->file('file.txt')->exists());

        return $directory;
    },
    before: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Renew/Recursive');
        $directory->make_recursive();
        $directory->file('file.txt')->create('content');

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->parent()->delete_recursive();
    }
);

test(
    title: 'it should create the directory recursively when directory not exists',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Renew/Recursive');

        $directory->renew_recursive();
        assert_true($directory->parent()->exists());
        assert_true($directory->exists());

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->parent()->delete_recursive();
    }
);
