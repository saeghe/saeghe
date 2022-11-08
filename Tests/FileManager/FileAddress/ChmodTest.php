<?php

namespace Tests\FileManager\FileAddress\ChmodTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;
use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should change file\'s permission',
    case: function () {
        $playGround = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->file('regular');
        $regular->create('content');
        $result = $regular->chmod(0664);
        assert_true($result instanceof FileAddress);
        assert_true($result->to_string() === $regular->to_string());
        assert_true(0664 === $regular->permission());

        $full = $playGround->file('full');
        $full->create('full');
        $full->chmod(0777);

        assert_true(0777 === $full->permission());

        return [$regular, $full];
    },
    after: function (FileAddress $regular, FileAddress $full) {
        $regular->delete();
        $full->delete();
    }
);
