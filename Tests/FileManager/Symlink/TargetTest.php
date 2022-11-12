<?php

namespace Tests\FileManager\Symlink\LinkTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\Symlink\target;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should return target path to the link',
    case: function (Path $file, Path $link) {
        assert_true($file->string() === target($link));

        return [$file, $link];
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/LinkSource');
        create($file, 'file content');
        $link = $file->parent()->append('symlink');
        link($file, $link);

        return [$file, $link];
    },
    after: function (Path $file, Path $link) {
        unlink($link);
        delete($file);
    }
);
