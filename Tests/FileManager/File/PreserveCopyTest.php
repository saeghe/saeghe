<?php

namespace Tests\FileManager\File\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\create;
use function Saeghe\Saeghe\FileManager\File\permission;
use function Saeghe\Saeghe\FileManager\File\preserve_copy;

test(
    title: 'it should preserve copy file',
    case: function (Path $first, Path $second) {
        $origin = $first->append('sample.txt');
        $destination = $second->append('sample.txt');

        assert_true(preserve_copy($origin->stringify(), $destination->stringify()));
        assert_true(file_exists($origin->stringify()), 'origin file does not exist after move!');
        assert_true(file_exists($destination->stringify()), 'destination file does not exist after move!');
        assert_true(0777 === permission($destination->stringify()));

        return [$first, $second];
    },
    before: function () {
        $first = Path::from_string(root() . 'Tests/PlayGround/first');
        $second = Path::from_string(root() . 'Tests/PlayGround/second');
        mkdir($first->stringify());
        mkdir($second->stringify());
        $file = $first->append('sample.txt');
        create($file->stringify(), 'sample text', 0777);

        return [$first, $second];
    },
    after: function (Path $first, Path $second) {
        delete_recursive($first->stringify());
        delete_recursive($second->stringify());
    }
);
