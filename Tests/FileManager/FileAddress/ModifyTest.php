<?php

namespace Tests\FileManager\FileAddress\ModifyTest;

use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should modify file',
    case: function (FileAddress $file) {
        $result = $file->modify('content in file');

        assert_true($result instanceof FileAddress);
        assert_true($result->to_string() === $file->to_string());
        assert_true($file->exists());
        assert_true('content in file' === $file->content());

        return $file;
    },
    before: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/sample.txt');
        $file->create('create content');

        return $file;
    },
    after: function (FileAddress $file) {
        $file->delete();
    }
);
