<?php

namespace Tests\FileManager\FileType\Json\WriteTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\clean;
use function Saeghe\Saeghe\FileManager\FileType\Json\to_array;
use function Saeghe\Saeghe\FileManager\FileType\Json\write;

test(
    title: 'it should write associated array to json file',
    case: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/File');
        write($file->to_string(), ['foo' => 'bar']);
        assert_true(['foo' => 'bar'] === to_array($file->to_string()));

        return $file;
    },
    after: function (Address $file) {
        clean($file->parent()->to_string());
    }
);
