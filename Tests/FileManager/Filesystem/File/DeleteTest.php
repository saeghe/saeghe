<?php

namespace Tests\FileManager\Filesystem\File\DeleteTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;
use function Saeghe\Saeghe\FileManager\File\exists;

test(
    title: 'it should delete a file',
    case: function (File $file) {
        $response = $file->delete();
        assert_true($file->stringify() === $response->stringify());
        assert_false(exists($file->stringify()));

        return $file;
    },
    before: function () {
        $file = new File(root() . 'Tests/PlayGround/File');
        $file->create('');

        return $file;
    }
);
