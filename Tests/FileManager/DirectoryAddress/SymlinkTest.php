<?php

namespace Tests\FileManager\DirectoryAddress\SymlinkTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;
use Saeghe\Saeghe\FileManager\SymlinkAddress;

test(
    title: 'it should return symlink for the given directory',
    case: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $result = $directory->symlink('symlink');

        assert_true($result instanceof SymlinkAddress);
        assert_true(
            DirectoryAddress::from_string(root() . 'Tests/PlayGround')->append('symlink')->to_string(),
            $result->to_string()
        );

        return $directory;
    }
);
