<?php

namespace Tests\FileManager\DirectoryAddress\ExistsTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should check if directory exists',
    case: function (DirectoryAddress $directory) {
        assert_false($directory->exists());
        $directory->make();
        assert_true($directory->exists());

        return $directory;
    },
    before: function () {
        return DirectoryAddress::from_string(root() . 'Tests/PlayGround/DirectoryAddress');
    },
    after: function (DirectoryAddress $directory) {
        $directory->delete();
    },
);
