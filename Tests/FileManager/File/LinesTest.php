<?php

namespace Tests\FileManager\File\LinesTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should read file\'s lines',
    case: function (Address $file) {
        foreach (File\lines($file->to_string()) as $n => $line) {
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
        $file = Address::from_string(root() . 'Tests/PlayGround/NewFile.txt');
        File\create($file->to_string(), 'First line.' . PHP_EOL . 'Second line.');

        return $file;
    },
    after: function (Address $file) {
        File\delete($file->to_string());
    }
);
