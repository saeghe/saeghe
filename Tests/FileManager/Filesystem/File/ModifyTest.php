<?php

namespace Tests\FileManager\Filesystem\File\ModifyTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;

test(
    title: 'it should modify file',
    case: function (File $file) {
        $result = $file->modify('content in file');

        assert_true($result instanceof File);
        assert_true($result->stringify() === $file->stringify());
        assert_true($file->exists());
        assert_true('content in file' === $file->content());

        return $file;
    },
    before: function () {
        $file = new File(root() . 'Tests/PlayGround/sample.txt');
        $file->create('create content');

        return $file;
    },
    after: function (File $file) {
        $file->delete();
    }
);
