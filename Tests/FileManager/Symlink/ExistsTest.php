<?php

namespace Tests\FileManager\Symlink\ExistsTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Symlink\exists;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should detect when link exists',
    case: function (Address $file) {
        $link = $file->parent()->append('symlink');
        assert_false(exists($link->to_string()));

        link($file->to_string(), $link->to_string());
        assert_true(exists($link->to_string()));

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
