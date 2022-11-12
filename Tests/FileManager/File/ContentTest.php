<?php

namespace Tests\FileManager\File\ContentTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\File\content;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should get file content',
    case: function (Path $file) {
        assert_true('sample text' === content($file));

        return $file;
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/sample.txt');
        file_put_contents($file, 'sample text');

        return $file;
    },
    after: function (Path $file) {
        delete($file);
    }
);
