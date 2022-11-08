<?php

namespace Tests\FileManager\FileAddress\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should preserve copy file',
    case: function (DirectoryAddress $first, DirectoryAddress $second) {
        $origin = $first->file('sample.txt');
        $destination = $second->file('sample.txt');
        $result = $origin->preserve_copy($destination);

        assert_true($result->to_string() === $origin->to_string());
        assert_true($origin->exists(), 'origin file does not exist after move!');
        assert_true($destination->exists(), 'destination file does not exist after move!');
        assert_true(0777 === $destination->permission());

        return [$first, $second];
    },
    before: function () {
        $first = DirectoryAddress::from_string(root() . 'Tests/PlayGround/first')->make();
        $second = DirectoryAddress::from_string(root() . 'Tests/PlayGround/second')->make();
        $first->file('sample.txt')->create('sample text', 0777);

        return [$first, $second];
    },
    after: function (DirectoryAddress $first, DirectoryAddress $second) {
        $first->delete_recursive();
        $second->delete_recursive();
    }
);
