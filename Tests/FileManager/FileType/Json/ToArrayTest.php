<?php

namespace Tests\FileManager\FileType\Json\ToArrayTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\clean;
use function Saeghe\Saeghe\FileManager\FileType\Json\to_array;

test(
    title: 'it should return associated array from json file',
    case: function (Path $file) {
        assert_true(['foo' => 'bar'] === to_array($file->stringify()));

        return $file;
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/File');
        file_put_contents($file->stringify(), json_encode(['foo' => 'bar']));

        return $file;
    },
    after: function (Path $file) {
        clean($file->parent()->stringify());
    }
);
