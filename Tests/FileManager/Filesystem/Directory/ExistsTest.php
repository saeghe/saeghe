<?php

namespace Tests\FileManager\Filesystem\Directory\ExistsTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should check if directory exists',
    case: function (Directory $directory) {
        assert_false($directory->exists());
        $directory->make();
        assert_true($directory->exists());

        return $directory;
    },
    before: function () {
        return Directory::from_string(root() . 'Tests/PlayGround/DirectoryAddress');
    },
    after: function (Directory $directory) {
        $directory->delete();
    },
);
