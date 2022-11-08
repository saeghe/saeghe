<?php

namespace Tests\FileManager\DirectoryAddress\SubdirectoryTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should return subdirectory for the given directory',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $result = $directory->subdirectory('Subdirectory');

        assert_true($result instanceof DirectoryAddress);
        assert_true(
            DirectoryAddress::from_string(root() . 'Tests/PlayGround')->append('Subdirectory')->to_string(),
            $result->to_string()
        );

        return $directory;
    }
);
