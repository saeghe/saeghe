<?php

namespace Tests\FileManager\SymlinkAddressTest;

use Saeghe\Saeghe\FileManager\FileAddress;
use Saeghe\Saeghe\FileManager\SymlinkAddress;
use  Saeghe\Saeghe\FileManager\File;
use  Saeghe\Saeghe\FileManager\SymLink;

test(
    title: 'it should link a symlink',
    case: function (FileAddress $file, SymlinkAddress $symlink) {
        $response = $symlink->link($file);
        assert_true($symlink->to_string() === $response->to_string());
        assert_true($file->exists());
        assert_true(Symlink\exists($symlink->to_string()));
        assert_true(Symlink\target($symlink->to_string()) === $file->to_string());

        return [$file, $symlink];
    },
    before: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/File');
        $file->create('');
        $symlink = SymlinkAddress::from_string(root() . 'Tests/PlayGround/Symlink');

        return [$file, $symlink];
    },
    after: function (FileAddress $file, SymlinkAddress $symlink) {
        Symlink\delete($symlink->to_string());
        File\delete($file->to_string());
    },
);

test(
    title: 'it should delete a symlink',
    case: function (FileAddress $file, SymlinkAddress $symlink) {
        $response = $symlink->delete();
        assert_true($symlink->to_string() === $response->to_string());
        assert_false(Symlink\exists($symlink->to_string()));

        return [$file, $symlink];
    },
    before: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/File');
        $file->create('');
        $symlink = SymlinkAddress::from_string(root() . 'Tests/PlayGround/Symlink');
        $symlink->link($file);

        return [$file, $symlink];
    }
);

test(
    title: 'it should check if symlink exists',
    case: function (FileAddress $file, SymlinkAddress $symlink) {
        assert_false($symlink->exists());
        $symlink->link($file);
        assert_true($symlink->exists());

        return [$file, $symlink];
    },
    before: function () {
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/File');
        $file->create('');
        $symlink = SymlinkAddress::from_string(root() . 'Tests/PlayGround/Symlink');

        return [$file, $symlink];
    },
    after: function (FileAddress $file, SymlinkAddress $symlink) {
        $symlink->delete();
        $file->delete();
    },
);
