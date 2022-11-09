<?php

namespace Tests\FileManager\Filesystem\Directory\ChmodTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should change directory\'s permission',
    case: function () {
        $playGround = new Directory(root() . 'Tests/PlayGround');
        $regular = $playGround->subdirectory('regular');
        $regular->make(0666);
        $result = $regular->chmod(0774);
        assert_true($result->stringify() === $regular->stringify(), 'It should return same directory');
        assert_true(0774 === $regular->permission(), 'Permission is not correct');

        $full = $playGround->subdirectory('full');
        $full->make(0755);
        $full->chmod(0777);

        assert_true(0777 === $full->permission(), '');

        return [$regular, $full];
    },
    after: function (Directory $regular, Directory $full) {
        $regular->delete();
        $full->delete();
    }
);
