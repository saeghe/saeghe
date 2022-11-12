<?php

namespace Tests\FileManager\Symlink\ExistsTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Symlink\exists;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should detect when link exists',
    case: function (Path $file) {
        $link = $file->parent()->append('symlink');
        assert_false(exists($link));

        link($file, $link);
        assert_true(exists($link));

        return [$file, $link];
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/LinkSource');
        create($file, 'file content');

        return $file;
    },
    after: function (Path $file, Path $link) {
        unlink($link);
        delete($file);
    }
);
