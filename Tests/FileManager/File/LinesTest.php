<?php

namespace Tests\FileManager\File\LinesTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should read file\'s lines',
    case: function (Path $file) {
        foreach (File\lines($file->stringify()) as $n => $line) {
            if ($n === 0) {
                assert_true('First line.' . PHP_EOL === $line, 'First line does not match in file lines.');
            }
            if ($n === 1) {
                assert_true('Second line.' === $line, 'First line does not match in file lines.');
            }
        }

        return $file;
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/NewFile.txt');
        File\create($file->stringify(), 'First line.' . PHP_EOL . 'Second line.');

        return $file;
    },
    after: function (Path $file) {
        File\delete($file->stringify());
    }
);
