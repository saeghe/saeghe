<?php

namespace Tests\FileManager\FileAddress\PermissionTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;
use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should return file\'s permission',
    case: function () {
        $playGround = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->file('regular');
        $regular->create('content');
        chmod($regular->to_string(), 0664);
        assert_true(0664 === $regular->permission());

        $full = $playGround->file('full');
        umask(0);
        $full->create('full');
        chmod($full->to_string(), 0777);
        assert_true(0777 === $full->permission());

        return [$regular, $full];
    },
    after: function (FileAddress $regular, FileAddress $full) {
        $regular->delete();
        $full->delete();
    }
);

test(
    title: 'it should not return cached permission',
    case: function () {
        $playGround = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $file = $playGround->file('regular');
        $file->create('', 0775);
        umask(0);
        chmod($file->to_string(), 0777);
        assert_true(0777 === $file->permission());
        chmod($file->to_string(), 0666);
        assert_true(0666 === $file->permission());

        return $file;
    },
    after: function (FileAddress $file) {
        $file->delete();
    }
);
