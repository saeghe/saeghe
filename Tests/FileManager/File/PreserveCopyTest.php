<?php

namespace Tests\FileManager\File\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\permission;
use function Saeghe\Saeghe\FileManager\File\preserve_copy;

test(
    title: 'it should preserve copy file',
    case: function (Address $first, Address $second) {
        $origin = $first->append('sample.txt');
        $destination = $second->append('sample.txt');

        assert_true(preserve_copy($origin->to_string(), $destination->to_string()));
        assert_true(file_exists($origin->to_string()), 'origin file does not exist after move!');
        assert_true(file_exists($destination->to_string()), 'destination file does not exist after move!');
        assert_true(0777 === permission($destination->to_string()));

        return [$first, $second];
    },
    before: function () {
        $first = Address::from_string(root() . 'Tests/PlayGround/first');
        $second = Address::from_string(root() . 'Tests/PlayGround/second');
        mkdir($first->to_string());
        mkdir($second->to_string());
        $file = $first->append('sample.txt');
        create($file->to_string(), 'sample text', 0777);

        return [$first, $second];
    },
    after: function (Address $first, Address $second) {
        delete_recursive($first->to_string());
        delete_recursive($second->to_string());
    }
);
