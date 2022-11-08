<?php

namespace Tests\FileManager\DirectoryAddress\ExistsOrCreate;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should return true when directory exists',
    case: function (DirectoryAddress $directory) {
        $result = $directory->exists_or_create();

        assert_true($result->to_string() === $directory->to_string());
        assert_true($result instanceof DirectoryAddress);

        return $directory;
    },
    before: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/ExistsOrCreate');
        $directory->make();

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->delete();
    }
);

test(
    title: 'it should create and return true when directory not exists',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/ExistsOrCreate');

        $result = $directory->exists_or_create();

        assert_true($result->to_string() === $directory->to_string());
        assert_true($directory->exists());

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->delete();
    }
);
