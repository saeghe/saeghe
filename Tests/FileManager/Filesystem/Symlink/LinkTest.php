<?php

namespace Tests\FileManager\Filesystem\Symlink\LinkTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should link a symlink',
    case: function (File $file, Symlink $symlink) {
        $response = $symlink->link($file);
        assert_true($symlink->stringify() === $response->stringify());
        assert_true($file->exists());
        assert_true(\file_exists($symlink->stringify()));
        assert_true(\readlink($symlink->stringify()) === $file->stringify());

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
