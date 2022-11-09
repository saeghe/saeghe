<?php

namespace Tests\FileManager\Directory\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should copy directory by preserving permission',
    case: function (Path $origin, Path $destination) {
        $copied_directory = $destination->append($origin->leaf());
        assert_true(Directory\preserve_copy($origin->stringify(), $copied_directory->stringify()));
        assert_true(Directory\exists($copied_directory->stringify()));
        assert_true(Directory\permission($origin->stringify()) === Directory\permission($copied_directory->stringify()));

        return [$origin, $destination];
    },
    before: function () {
        $origin = Path::from_string(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        Directory\make_recursive($origin->stringify());
        $destination = Path::from_string(root() . 'Tests/PlayGround/Destination');
        Directory\make($destination->stringify());

        return [$origin, $destination];
    },
    after: function (Path $origin, Path $destination) {
        Directory\delete_recursive($origin->parent()->stringify());
        Directory\delete_recursive($destination->stringify());
    }
);

test(
    title: 'it should copy directory by preserving permission with any permission',
    case: function (Path $origin, Path $destination) {
        $copied_directory = $destination->append($origin->leaf());
        assert_true(Directory\preserve_copy($origin->stringify(), $copied_directory->stringify()));
        assert_true(Directory\exists($copied_directory->stringify()));
        assert_true(0777 === Directory\permission($copied_directory->stringify()));

        return [$origin, $destination];
    },
    before: function () {
        $origin = Path::from_string(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        Directory\make_recursive($origin->stringify(), 0777);
        $destination = Path::from_string(root() . 'Tests/PlayGround/Destination');
        Directory\make($destination->stringify());

        return [$origin, $destination];
    },
    after: function (Path $origin, Path $destination) {
        Directory\delete_recursive($origin->parent()->stringify());
        Directory\delete_recursive($destination->stringify());
    }
);
