<?php

namespace Tests\FileManager\Symlink\DeleteTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should delete a symlink',
    case: function (File $file, Symlink $symlink) {
        $response = $symlink->delete();
        assert_true($symlink->path->string() === $response->path->string());
        assert_false($symlink->exists());

        return [$file, $symlink];
    },
    before: function () {
        $file = File::from_string(root() . 'Tests/PlayGround/File');
        $file->create('');
        $symlink = Symlink::from_string(root() . 'Tests/PlayGround/Symlink');
        $symlink->link($file);

        return [$file, $symlink];
    }
);
