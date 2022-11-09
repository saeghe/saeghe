<?php

namespace Tests\FileManager\File\ModifyTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\File\content;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\modify;
use function Saeghe\Saeghe\FileManager\File\delete;
use function Saeghe\Saeghe\FileManager\File\exists;

test(
    title: 'it should modify file',
    case: function (Path $file) {
        assert_true(modify($file->stringify(), 'content in file'));
        assert_true(exists($file->stringify()));
        assert_true('content in file' === content($file->stringify()));

        return $file;
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/sample.txt');
        create($file->stringify(), 'create content');

        return $file;
    },
    after: function (Path $file) {
        delete($file->stringify());
    }
);
