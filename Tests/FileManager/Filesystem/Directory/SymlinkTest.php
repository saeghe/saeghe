<?php

namespace Tests\FileManager\Filesystem\Directory\SymlinkTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should return symlink for the given directory',
    case: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround');
        $result = $directory->symlink('symlink');

        assert_true($result instanceof Symlink);
        assert_true(
            Directory::from_string(root() . 'Tests/PlayGround')->append('symlink')->string()
            ===
            $result->path->string()
        );

        return $directory;
    }
);
