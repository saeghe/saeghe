<?php

namespace Tests\FileManager\Symlink\DeleteTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\File;
use function Saeghe\Saeghe\FileManager\Symlink\link;
use function Saeghe\Saeghe\FileManager\Symlink\delete;

test(
    title: 'it should delete the link',
    case: function (Address $file, Address $link) {
        assert_true(delete($link->to_string()));
        assert_true($file->exists());
        assert_false($link->exists());

        return $file;
    },
    before: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/LinkSource');
        File\create($file->to_string(), 'file content');
        $link = $file->parent()->append('symlink');
        link($file->to_string(), $link->to_string());

        return [$file, $link];
    },
    after: function (Address $file) {
        File\delete($file->to_string());
    }
);
