<?php

namespace Tests\FileManager\File\ContentTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\File\content;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should create file',
    case: function (Address $file) {
        assert_true('sample text' === content($file->to_string()));

        return $file;
    },
    before: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/sample.txt');
        file_put_contents($file->to_string(), 'sample text');

        return $file;
    },
    after: function (Address $file) {
        delete($file->to_string());
    }
);
