<?php

namespace Tests\FileManager\Symlink\DeleteTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\File;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\Symlink\delete;

test(
    title: 'it should delete the link',
    case: function (Path $file, Path $link) {
        assert_true(delete($link));
        assert_true($file->exists());
        assert_false($link->exists());

        return $file;
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/LinkSource');
        File\create($file, 'file content');
        $link = $file->parent()->append('symlink');
        link($file->as_file(), $link);

        return [$file, $link];
    },
    after: function (Path $file) {
        File\delete($file);
    }
);
