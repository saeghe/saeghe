<?php

namespace Tests\FileManager\Filesystem\File\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should preserve copy file',
    case: function (Directory $first, Directory $second) {
        $origin = $first->file('sample.txt');
        $destination = $second->file('sample.txt');
        $result = $origin->preserve_copy($destination);

        assert_true($result->path->string() === $origin->path->string());
        assert_true($origin->exists(), 'origin file does not exist after move!');
        assert_true($destination->exists(), 'destination file does not exist after move!');
        assert_true(0777 === $destination->permission());

        return [$first, $second];
    },
    before: function () {
        $first = Directory::from_string(root() . 'Tests/PlayGround/first')->make();
        $second = Directory::from_string(root() . 'Tests/PlayGround/second')->make();
        $first->file('sample.txt')->create('sample text', 0777);

        return [$first, $second];
    },
    after: function (Directory $first, Directory $second) {
        $first->delete_recursive();
        $second->delete_recursive();
    }
);
