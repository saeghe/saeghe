<?php

namespace Tests\FileManager\File\MoveTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\move;

test(
    title: 'it should delete file',
    case: function (Address $first, Address $second) {
        $origin = $first->append('sample.txt');
        $destination = $second->append('sample.txt');

        assert_true(move($origin->to_string(), $destination->to_string()));

        assert_false(file_exists($origin->to_string()), 'origin file exists after move!');
        assert_true(file_exists($destination->to_string()), 'destination file does not exist after move!');

        return [$first, $second];
    },
    before: function () {
        $first = Address::from_string(root() . 'Tests/PlayGround/first');
        $second = Address::from_string(root() . 'Tests/PlayGround/second');
        mkdir($first->to_string());
        mkdir($second->to_string());
        $file = $first->append('sample.txt');
        file_put_contents($file->to_string(), 'sample text');

        return [$first, $second];
    },
    after: function (Address $first, Address $second) {
        delete_recursive($first->to_string());
        delete_recursive($second->to_string());
    }
);
