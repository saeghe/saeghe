<?php

namespace Tests\FileManager\DirectoryAddress\ChmodTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should change directory\'s permission',
    case: function () {
        $playGround = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->subdirectory('regular');
        $regular->make(0666);
        $result = $regular->chmod(0774);
        assert_true($result->to_string() === $regular->to_string(), 'It should return same directory');
        assert_true(0774 === $regular->permission(), 'Permission is not correct');

        $full = $playGround->subdirectory('full');
        $full->make(0755);
        $full->chmod(0777);

        assert_true(0777 === $full->permission(), '');

        return [$regular, $full];
    },
    after: function (DirectoryAddress $regular, DirectoryAddress $full) {
        $regular->delete();
        $full->delete();
    }
);
