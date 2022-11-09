<?php

namespace Tests\FileManager\Filesystem\File\LinesTest;

use Saeghe\Saeghe\FileManager\Filesystem\File;

test(
    title: 'it should read file\'s lines',
    case: function (File $file) {
        foreach ($file->lines() as $n => $line) {
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
        $file = new File(root() . 'Tests/PlayGround/NewFile.txt');
        $file->create('First line.' . PHP_EOL . 'Second line.');

        return $file;
    },
    after: function (File $file) {
        $file->delete();
    }
);
