<?php

namespace Tests\FileManager\Filesystem\Directory\makeTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should make a directory',
    case: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        $result = $directory->make();

        assert_true($result->path->string() === $directory->path->string());
        assert_true($directory->exists());
        assert_true(0775 === $directory->permission());

        return $directory;
    },
    after: function (Directory $address) {
        $address->delete();
    }
);

test(
    title: 'it should make a directory with the given permission',
    case: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        $directory->make(0777);

        assert_true($directory->exists());
        assert_true(0777 === $directory->permission());

        return $directory;
    },
    after: function (Directory $address) {
        $address->delete();
    }
);
