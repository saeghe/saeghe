<?php

namespace Tests\FileManager\Filesystem\Directory\RenewRecursiveTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should clean directory when directory exists',
    case: function (Directory $directory) {
        $result = $directory->renew_recursive();

        assert_true($result->path->string() === $directory->path->string());
        assert_true($directory->exists());
        assert_false($directory->file('file.txt')->exists());

        return $directory;
    },
    before: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround/Renew/Recursive');
        $directory->make_recursive();
        $directory->file('file.txt')->create('content');

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->parent()->delete_recursive();
    }
);

test(
    title: 'it should create the directory recursively when directory not exists',
    case: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround/Renew/Recursive');

        $directory->renew_recursive();
        assert_true($directory->parent()->exists());
        assert_true($directory->exists());

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->parent()->delete_recursive();
    }
);
