<?php

namespace Tests\FileManager\Symlink\LinkTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should create a link to the given source',
    case: function (Address $file) {
        $link = $file->parent()->append('symlink');

        assert_true(link($file->to_string(), $link->to_string()));
        assert_true($file->to_string(), readlink($link->to_string()));

        return [$file, $link];
    },
    before: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/LinkSource');
        create($file->to_string(), 'file content');

        return $file;
    },
    after: function (Address $file, Address $link) {
        unlink($link->to_string());
        delete($file->to_string());
    }
);
