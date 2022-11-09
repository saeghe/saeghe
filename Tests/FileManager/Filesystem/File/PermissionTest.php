<?php

namespace Tests\FileManager\Filesystem\File\PermissionTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;

test(
    title: 'it should return file\'s permission',
    case: function () {
        $playGround = new Directory(root() . 'Tests/PlayGround');
        $regular = $playGround->file('regular');
        $regular->create('content');
        chmod($regular->stringify(), 0664);
        assert_true(0664 === $regular->permission());

        $full = $playGround->file('full');
        umask(0);
        $full->create('full');
        chmod($full->stringify(), 0777);
        assert_true(0777 === $full->permission());

        return [$regular, $full];
    },
    after: function (File $regular, File $full) {
        $regular->delete();
        $full->delete();
    }
);

test(
    title: 'it should not return cached permission',
    case: function () {
        $playGround = new Directory(root() . 'Tests/PlayGround');
        $file = $playGround->file('regular');
        $file->create('', 0775);
        umask(0);
        chmod($file->stringify(), 0777);
        assert_true(0777 === $file->permission());
        chmod($file->stringify(), 0666);
        assert_true(0666 === $file->permission());

        return $file;
    },
    after: function (File $file) {
        $file->delete();
    }
);
