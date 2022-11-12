<?php

namespace Tests\FileManager\Symlink\ExistsTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should check if symlink exists',
    case: function (File $file, Symlink $symlink) {
        assert_false($symlink->exists());
        $symlink->link($file);
        assert_true($symlink->exists());

        return [$file, $symlink];
    },
    before: function () {
        $file = File::from_string(root() . 'Tests/PlayGround/File');
        $file->create('');
        $symlink = Symlink::from_string(root() . 'Tests/PlayGround/Symlink');

        return [$file, $symlink];
    },
    after: function (File $file, Symlink $symlink) {
        $symlink->delete();
        $file->delete();
    },
);
