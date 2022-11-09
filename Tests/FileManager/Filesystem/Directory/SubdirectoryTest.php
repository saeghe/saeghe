<?php

namespace Tests\FileManager\Filesystem\Directory\SubdirectoryTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should return subdirectory for the given directory',
    case: function () {
        $directory = new Directory(root() . 'Tests/PlayGround');
        $result = $directory->subdirectory('Subdirectory');

        assert_true($result instanceof Directory);
        assert_true(
            (new Directory(root() . 'Tests/PlayGround'))->append('Subdirectory')->stringify(),
            $result->stringify()
        );

        return $directory;
    }
);
