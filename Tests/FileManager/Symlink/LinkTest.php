<?php

namespace Tests\FileManager\Symlink\LinkTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should create a link to the given source',
    case: function (Path $file) {
        $link = $file->parent()->append('symlink');

        assert_true(link($file->stringify(), $link->stringify()));
        assert_true($file->stringify(), readlink($link->stringify()));

        return [$file, $link];
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/LinkSource');
        create($file->stringify(), 'file content');

        return $file;
    },
    after: function (Path $file, Path $link) {
        unlink($link->stringify());
        delete($file->stringify());
    }
);
