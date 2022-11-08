<?php

namespace Tests\FileManager\DirectoryAddress\RenewTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should clean directory when directory exists',
    case: function (DirectoryAddress $directory) {
        $result = $directory->renew();

        assert_true($result->to_string() === $directory->to_string());
        assert_true($directory->exists());
        assert_false($directory->file('file.txt')->exists());

        return $directory;
    },
    before: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Renew');
        $directory->make();
        $directory->file('file.txt')->create('content');

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->delete_recursive();
    }
);

test(
    title: 'it should create the directory when directory not exists',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Renew');

        $directory->renew();
        assert_true($directory->exists());

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->delete_recursive();
    }
);
