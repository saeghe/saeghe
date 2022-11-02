<?php

namespace Tests\FileManager\File\ExistsTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\File\exists;
use function Saeghe\Saeghe\FileManager\Directory\flush;

test(
    title: 'it should return false when file is not exists',
    case: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/IsExists');
        assert(! exists($file->to_string()), 'File/exists is not working!');
    }
);

test(
    title: 'it should return false when given path is a directory',
    case: function (Address $file) {
        assert(! exists($file->to_string()), 'File/exists is not working!');

        return $file;
    },
    before: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/file');

        mkdir($file->to_string());

        return $file;
    },
    after: function (Address $file) {
        flush($file->parent()->to_string());
    }
);

test(
    title: 'it should return true when file is exist and is a file',
    case: function (Address $file) {
        assert(exists($file->to_string()), 'File/exists is not working!');

        return $file;
    },
    before: function () {
        $file = Address::from_string(root() . 'Tests/PlayGround/File');
        file_put_contents($file->to_string(), 'content');

        return $file;
    },
    after: function (Address $file) {
        flush($file->parent()->to_string());
    }
);
