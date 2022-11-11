<?php

namespace Tests\FileManager\Filesystem\Directory\SubdirectoryTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should return subdirectory for the given directory',
    case: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround');
        $result = $directory->subdirectory('Subdirectory');

        assert_true($result instanceof Directory);
        assert_true(
            Directory::from_string(root() . 'Tests/PlayGround')->append('Subdirectory')->string()
            ===
            $result->path->string()
        );

        return $directory;
    }
);
