<?php

namespace Tests\FileManager\File\DeleteFileTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\File\delete;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should delete file',
    case: function (Path $file) {
        assert_true(delete($file));
        assert_false(file_exists($file), 'delete file is not working!');
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/sample.txt');
        file_put_contents($file, 'sample text');

        return $file;
    }
);
