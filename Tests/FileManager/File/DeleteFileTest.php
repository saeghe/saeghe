<?php

namespace Tests\FileManager\File\DeleteFileTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should delete file',
    case: function (Address $file) {
        assert(delete($file->toString()));
        assert(! file_exists($file->toString()), 'delete file is not working!');
    },
    before: function () {
        $file = Address::fromString(__DIR__ . '/sample.txt');
        file_put_contents($file->toString(), 'sample text');

        return $file;
    }
);
