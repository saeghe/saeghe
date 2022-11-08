<?php

namespace Tests\FileManager\DirectoryAddress\FileTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;
use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should return file for the given directory',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $result = $directory->file('filename');

        assert_true($result instanceof FileAddress);
        assert_true(
            DirectoryAddress::from_string(root() . 'Tests/PlayGround')->append('filename')->to_string(),
            $result->to_string()
        );

        return $directory;
    }
);
