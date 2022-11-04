<?php

namespace Tests\FileManager\Directory\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should copy directory by preserving permission',
    case: function (Address $origin, Address $destination) {
        $copied_directory = $destination->append($origin->leaf());
        assert(Directory\preserve_copy($origin->to_string(), $copied_directory->to_string()));
        assert(Directory\exists($copied_directory->to_string()));
        assert(Directory\permission($origin->to_string()) === Directory\permission($copied_directory->to_string()));

        return [$origin, $destination];
    },
    before: function () {
        $origin = Address::from_string(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        Directory\make_recursive($origin->to_string());
        $destination = Address::from_string(root() . 'Tests/PlayGround/Destination');
        Directory\make($destination->to_string());

        return [$origin, $destination];
    },
    after: function (Address $origin, Address $destination) {
        Directory\delete_recursive($origin->parent()->to_string());
        Directory\delete_recursive($destination->to_string());
    }
);

test(
    title: 'it should copy directory by preserving permission with any permission',
    case: function (Address $origin, Address $destination) {
        $copied_directory = $destination->append($origin->leaf());
        assert(Directory\preserve_copy($origin->to_string(), $copied_directory->to_string()));
        assert(Directory\exists($copied_directory->to_string()));
        assert(0777 === Directory\permission($copied_directory->to_string()));

        return [$origin, $destination];
    },
    before: function () {
        $origin = Address::from_string(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        Directory\make_recursive($origin->to_string(), 0777);
        $destination = Address::from_string(root() . 'Tests/PlayGround/Destination');
        Directory\make($destination->to_string());

        return [$origin, $destination];
    },
    after: function (Address $origin, Address $destination) {
        Directory\delete_recursive($origin->parent()->to_string());
        Directory\delete_recursive($destination->to_string());
    }
);
