<?php

namespace Tests\FileManager\File\DeleteFileTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should delete file',
    case: function (Address $file) {
        assert(delete($file->to_string()));
        assert(! file_exists($file->to_string()), 'delete file is not working!');
    },
    before: function () {
        $file = Address::from_string(__DIR__ . '/sample.txt');
        file_put_contents($file->to_string(), 'sample text');

        return $file;
    }
);
