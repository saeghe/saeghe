<?php

namespace Tests\FileManager\Symlink\DeleteTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should delete a symlink',
    case: function (File $file, Symlink $symlink) {
        $response = $symlink->delete();
        assert_true($symlink->stringify() === $response->stringify());
        assert_false($symlink->exists());

        return [$file, $symlink];
    },
    before: function () {
        $file = new File(root() . 'Tests/PlayGround/File');
        $file->create('');
        $symlink = new Symlink(root() . 'Tests/PlayGround/Symlink');
        $symlink->link($file);

        return [$file, $symlink];
    }
);
