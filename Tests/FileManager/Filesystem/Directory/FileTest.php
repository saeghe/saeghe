<?php

namespace Tests\FileManager\Filesystem\Directory\FileTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;

test(
    title: 'it should return file for the given directory',
    case: function () {
        $directory = new Directory(root() . 'Tests/PlayGround');
        $result = $directory->file('filename');

        assert_true($result instanceof File);
        assert_true(
            (new Directory(root() . 'Tests/PlayGround'))->append('filename')->stringify(),
            $result->stringify()
        );

        return $directory;
    }
);
