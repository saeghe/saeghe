<?php

namespace Tests\FileManager\FileType\Json\ToArrayTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\FileManager\FileType\Json\to_array;

test(
    title: 'it should return associated array from json file',
    case: function (Address $file) {
        assert(['foo' => 'bar'] === to_array($file->to_string()));

        return $file;
    },
    before: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/File');
        file_put_contents($file->to_string(), json_encode(['foo' => 'bar']));

        return $file;
    },
    after: function (Address $file) {
        flush($file->parent()->to_string());
    }
);
