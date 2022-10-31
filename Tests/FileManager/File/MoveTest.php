<?php

namespace Tests\FileManager\File\MoveTest;

use Saeghe\Saeghe\Path;
use function Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\FileManager\File\move;

test(
    title: 'it should delete file',
    case: function (Path $first, Path $second) {
        $origin = $first->append('sample.txt');
        $destination = $second->append('sample.txt');

        assert(move($origin->toString(), $destination->toString()));

        assert(! file_exists($origin->toString()), 'origin file exists after move!');
        assert(file_exists($destination->toString()), 'destination file does not exist after move!');

        return [$first, $second];
    },
    before: function () {
        $first = Path::fromString(__DIR__ . '/first');
        $second = Path::fromString(__DIR__ . '/second');
        mkdir($first->toString());
        mkdir($second->toString());
        $file = $first->append('sample.txt');
        file_put_contents($file->toString(), 'sample text');

        return [$first, $second];
    },
    after: function (Path $first, Path $second) {
        delete_recursive($first->toString());
        delete_recursive($second->toString());
    }
);
