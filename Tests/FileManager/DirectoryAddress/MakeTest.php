<?php

namespace Tests\FileManager\DirectoryAddress\makeTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should make a directory',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        $result = $directory->make();

        assert_true($result->to_string() === $directory->to_string());
        assert_true($directory->exists());
        assert_true(0775 === $directory->permission());

        return $directory;
    },
    after: function (DirectoryAddress $address) {
        $address->delete();
    }
);

test(
    title: 'it should make a directory with the given permission',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        $directory->make(0777);

        assert_true($directory->exists());
        assert_true(0777 === $directory->permission());

        return $directory;
    },
    after: function (DirectoryAddress $address) {
        $address->delete();
    }
);
