<?php

namespace Tests\FileManager\Filesystem\File\ModifyTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should modify file',
    case: function (File $file) {
        $result = $file->modify('content in file');

        assert_true($result instanceof File);
        assert_true($result->path->string() === $file->path->string());
        assert_true($file->exists());
        assert_true('content in file' === $file->content());

        return $file;
    },
    before: function () {
        $file = File::from_string(root() . 'Tests/PlayGround/sample.txt');
        $file->create('create content');

        return $file;
    },
    after: function (File $file) {
        $file->delete();
    }
);
