<?php

namespace Tests\FileManager\FileAddress\CreateTest;

use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should create file',
    case: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/sample.txt');
        $result = $file->create('content in file');

        assert_true($result->to_string() === $file->to_string());
        assert_true($file->exists());
        assert_true('content in file' === $file->content());
        assert_true(0664 === $file->permission());

        return $file;
    },
    after: function (FileAddress $file) {
        $file->delete();
    }
);

test(
    title: 'it should create file with given permission',
    case: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/sample.txt');
        $file->create('content in file', 0765);

        assert_true($file->exists());
        assert_true('content in file' === $file->content());
        assert_true(0765 === $file->permission());

        return $file;
    },
    after: function (FileAddress $file) {
        $file->delete();
    }
);
