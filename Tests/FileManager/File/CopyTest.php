<?php

namespace Tests\FileManager\File\CopyTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\copy;
use function Saeghe\Saeghe\FileManager\File\create;

test(
    title: 'it should copy file',
    case: function (Path $first, Path $second) {
        $origin = $first->append('sample.txt');
        $destination = $second->append('sample.txt');

        assert_true(copy($origin->stringify(), $destination->stringify()));
        assert_true(file_exists($origin->stringify()), 'origin file does not exist after move!');
        assert_true(file_exists($destination->stringify()), 'destination file does not exist after move!');

        return [$first, $second];
    },
    before: function () {
        $first = Path::from_string(root() . 'Tests/PlayGround/first');
        $second = Path::from_string(root() . 'Tests/PlayGround/second');
        mkdir($first->stringify());
        mkdir($second->stringify());
        $file = $first->append('sample.txt');
        create($file->stringify(), 'sample text');

        return [$first, $second];
    },
    after: function (Path $first, Path $second) {
        delete_recursive($first->stringify());
        delete_recursive($second->stringify());
    }
);
