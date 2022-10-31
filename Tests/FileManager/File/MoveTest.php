<?php

namespace Tests\FileManager\File\MoveTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\move;

test(
    title: 'it should delete file',
    case: function (Address $first, Address $second) {
        $origin = $first->append('sample.txt');
        $destination = $second->append('sample.txt');

        assert(move($origin->toString(), $destination->toString()));

        assert(! file_exists($origin->toString()), 'origin file exists after move!');
        assert(file_exists($destination->toString()), 'destination file does not exist after move!');

        return [$first, $second];
    },
    before: function () {
        $first = Address::fromString(__DIR__ . '/first');
        $second = Address::fromString(__DIR__ . '/second');
        mkdir($first->toString());
        mkdir($second->toString());
        $file = $first->append('sample.txt');
        file_put_contents($file->toString(), 'sample text');

        return [$first, $second];
    },
    after: function (Address $first, Address $second) {
        delete_recursive($first->toString());
        delete_recursive($second->toString());
    }
);
