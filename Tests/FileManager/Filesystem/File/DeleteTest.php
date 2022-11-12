<?php

namespace Tests\FileManager\Filesystem\File\DeleteTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use function Saeghe\Saeghe\FileManager\File\exists;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should delete a file',
    case: function (File $file) {
        $response = $file->delete();
        assert_true($file->path->string() === $response->path->string());
        assert_false(exists($file));

        return $file;
    },
    before: function () {
        $file = File::from_string(root() . 'Tests/PlayGround/File');
        $file->create('');

        return $file;
    }
);
