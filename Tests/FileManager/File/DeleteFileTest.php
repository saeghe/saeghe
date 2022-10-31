<?php

namespace Tests\FileManager\File\DeleteFileTest;

use Saeghe\Saeghe\Path;
use function Saeghe\FileManager\File\delete;

test(
    title: 'it should delete file',
    case: function (Path $file) {
        assert(delete($file->toString()));
        assert(! file_exists($file->toString()), 'delete file is not working!');
    },
    before: function () {
        $file = Path::fromString(__DIR__ . '/sample.txt');
        file_put_contents($file->toString(), 'sample text');

        return $file;
    }
);
