<?php

namespace Tests\FileManager\Symlink\ExistsTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Symlink\exists;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should detect when link exists',
    case: function (Path $file) {
        $link = $file->parent()->append('symlink');
        assert_false(exists($link->stringify()));

        link($file->stringify(), $link->stringify());
        assert_true(exists($link->stringify()));

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
