<?php

namespace Tests\FileManager\Filesystem\File\ContentTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;

test(
    title: 'it should get file content',
    case: function (File $file) {
        assert_true('sample text' === $file->content());

        return $file;
    },
    before: function () {
        $file = File::from_string(root() . 'Tests/PlayGround/sample.txt');
        $file->create('sample text');

        return $file;
    },
    after: function (File $file) {
        $file->delete();
    }
);
