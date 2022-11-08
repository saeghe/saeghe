<?php

namespace Tests\FileManager\FileAddress\DeleteTest;

use Saeghe\Saeghe\FileManager\FileAddress;
use function Saeghe\Saeghe\FileManager\File\exists;

test(
    title: 'it should delete a file',
    case: function (FileAddress $file) {
        $response = $file->delete();
        assert_true($file->to_string() === $response->to_string());
        assert_false(exists($file->to_string()));

        return $file;
    },
    before: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/File');
        $file->create('');

        return $file;
    }
);
