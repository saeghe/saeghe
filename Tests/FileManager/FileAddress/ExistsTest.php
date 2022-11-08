<?php

namespace Tests\FileManager\FileAddress\ExistsTest;

use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should check if symlink exists',
    case: function (FileAddress $file) {
        assert_false($file->exists());
        $file->create('');
        assert_true($file->exists());

        return $file;
    },
    before: function () {
        return FileAddress::from_string(root() . 'Tests/PlayGround/File');
    },
    after: function (FileAddress $file) {
        $file->delete();
    },
);
