<?php

namespace Tests\FileManager\Filesystem\Directory\RenewTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should clean directory when directory exists',
    case: function (Directory $directory) {
        $result = $directory->renew();

        assert_true($result->stringify() === $directory->stringify());
        assert_true($directory->exists());
        assert_false($directory->file('file.txt')->exists());

        return $directory;
    },
    before: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/Renew');
        $directory->make();
        $directory->file('file.txt')->create('content');

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete_recursive();
    }
);

test(
    title: 'it should create the directory when directory not exists',
    case: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/Renew');

        $directory->renew();
        assert_true($directory->exists());

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete_recursive();
    }
);
