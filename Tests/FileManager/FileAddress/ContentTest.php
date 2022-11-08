<?php

namespace Tests\FileManager\FileAddress\ContentTest;

use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should get file content',
    case: function (FileAddress $file) {
        assert_true('sample text' === $file->content());

        return $file;
    },
    before: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/sample.txt');
        $file->create('sample text');

        return $file;
    },
    after: function (FileAddress $file) {
        $file->delete();
    }
);
