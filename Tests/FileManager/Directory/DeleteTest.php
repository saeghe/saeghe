<?php

namespace Tests\FileManager\Directory\DeleteTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should delete the given directory',
    case: function (Path $directory) {
        Directory\delete($directory);

        assert_false(Directory\exists($directory));
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/DeleteDirectory');
        mkdir($directory);

        return $directory;
    }
);
