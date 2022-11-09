<?php

namespace Tests\FileManager\Filesystem\Directory\ExistsOrCreate;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should return true when directory exists',
    case: function (Directory $directory) {
        $result = $directory->exists_or_create();

        assert_true($result->stringify() === $directory->stringify());
        assert_true($result instanceof Directory);

        return $directory;
    },
    before: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/ExistsOrCreate');
        $directory->make();

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete();
    }
);

test(
    title: 'it should create and return true when directory not exists',
    case: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/ExistsOrCreate');

        $result = $directory->exists_or_create();

        assert_true($result->stringify() === $directory->stringify());
        assert_true($directory->exists());

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete();
    }
);
