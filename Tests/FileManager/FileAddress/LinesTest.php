<?php

namespace Tests\FileManager\FileAddress\LinesTest;

use Saeghe\Saeghe\FileManager\FileAddress;

test(
    title: 'it should read file\'s lines',
    case: function (FileAddress $file) {
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
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/NewFile.txt');
        $file->create('First line.' . PHP_EOL . 'Second line.');

        return $file;
    },
    after: function (FileAddress $file) {
        $file->delete();
    }
);
