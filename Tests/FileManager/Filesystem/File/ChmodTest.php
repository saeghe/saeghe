<?php

namespace Tests\FileManager\Filesystem\File\ChmodTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;

test(
    title: 'it should change file\'s permission',
    case: function () {
        $playGround = new Directory(root() . 'Tests/PlayGround');
        $regular = $playGround->file('regular');
        $regular->create('content');
        $result = $regular->chmod(0664);
        assert_true($result instanceof File);
        assert_true($result->stringify() === $regular->stringify());
        assert_true(0664 === $regular->permission());

        $full = $playGround->file('full');
        $full->create('full');
        $full->chmod(0777);

        assert_true(0777 === $full->permission());

        return [$regular, $full];
    },
    after: function (File $regular, File $full) {
        $regular->delete();
        $full->delete();
    }
);
