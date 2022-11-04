<?php

namespace Tests\FileManager\Directory\RenewTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should clean directory when directory exists',
    case: function (Address $directory) {
        Directory\renew($directory->to_string());
        assert(Directory\exists($directory->to_string()));
        assert(! File\exists($directory->append('file.txt')->to_string()));

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Renew');
        Directory\make($directory->to_string());
        file_put_contents($directory->append('file.txt')->to_string(), 'content');

        return $directory;
    },
    after: function (Address $directory) {
        Directory\delete_recursive($directory->to_string());
    }
);

test(
    title: 'it should create the directory when directory not exists',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Renew');

        Directory\renew($directory->to_string());
        assert(Directory\exists($directory->to_string()));

        return $directory;
    },
    after: function (Address $directory) {
        Directory\delete_recursive($directory->to_string());
    }
);
