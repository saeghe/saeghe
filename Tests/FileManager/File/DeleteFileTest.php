<?php

namespace Tests\FileManager\File\DeleteFileTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should delete file',
    case: function (Path $file) {
        assert_true(delete($file->stringify()));
        assert_false(file_exists($file->stringify()), 'delete file is not working!');
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/sample.txt');
        file_put_contents($file->stringify(), 'sample text');

        return $file;
    }
);
