<?php

namespace Tests\FileManager\Filesystem\File\ExistsTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;

test(
    title: 'it should check if symlink exists',
    case: function (File $file) {
        assert_false($file->exists());
        $file->create('');
        assert_true($file->exists());

        return $file;
    },
    before: function () {
        return File::from_string(root() . 'Tests/PlayGround/File');
    },
    after: function (File $file) {
        $file->delete();
    },
);
