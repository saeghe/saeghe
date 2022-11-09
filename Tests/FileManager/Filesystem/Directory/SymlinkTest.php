<?php

namespace Tests\FileManager\Filesystem\Directory\SymlinkTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should return symlink for the given directory',
    case: function () {
        $directory = new Directory(root() . 'Tests/PlayGround');
        $result = $directory->symlink('symlink');

        assert_true($result instanceof Symlink);
        assert_true(
            (new Directory(root() . 'Tests/PlayGround'))->append('symlink')->stringify(),
            $result->stringify()
        );

        return $directory;
    }
);
