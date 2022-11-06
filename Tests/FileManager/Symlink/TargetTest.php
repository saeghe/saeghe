<?php

namespace Tests\FileManager\Symlink\LinkTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\Symlink\target;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should return target path to the link',
    case: function (Address $file, Address $link) {
        assert_true($file->to_string(), target($link->to_string()));

        return [$file, $link];
    },
    before: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/LinkSource');
        create($file->to_string(), 'file content');
        $link = $file->parent()->append('symlink');
        link($file->to_string(), $link->to_string());

        return [$file, $link];
    },
    after: function (Address $file, Address $link) {
        unlink($link->to_string());
        delete($file->to_string());
    }
);
