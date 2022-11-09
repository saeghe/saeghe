<?php

namespace Tests\FileManager\Symlink\ExistsTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should check if symlink exists',
    case: function (File $file, Symlink $symlink) {
        assert_false($symlink->exists());
        $symlink->link($file);
        assert_true($symlink->exists());

        return [$file, $symlink];
    },
    before: function () {
        $file = new File(root() . 'Tests/PlayGround/File');
        $file->create('');
        $symlink = new Symlink(root() . 'Tests/PlayGround/Symlink');

        return [$file, $symlink];
    },
    after: function (File $file, Symlink $symlink) {
        $symlink->delete();
        $file->delete();
    },
);
