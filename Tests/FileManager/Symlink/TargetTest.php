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
        assert_true($file->stringify(), target($link->stringify()));

        return [$file, $link];
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/LinkSource');
        create($file->stringify(), 'file content');
        $link = $file->parent()->append('symlink');
        link($file->stringify(), $link->stringify());

        return [$file, $link];
    },
    after: function (Path $file, Path $link) {
        unlink($link->stringify());
        delete($file->stringify());
    }
);
