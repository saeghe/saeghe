<?php

namespace Tests\FileManager\Directory\PermissionTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should return directory\'s permission',
    case: function () {
        $playGround = Address::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        mkdir($regular->to_string(), 0755);
        assert(0755 === Directory\permission($regular->to_string()));

        $full = $playGround->append('full');
        umask(0);
        mkdir($full->to_string(), 0777);
        assert(0777, Directory\permission($full->to_string()));

        return [$regular, $full];
    },
    after: function (Address $regular, Address $full) {
        Directory\delete($regular->to_string());
        Directory\delete($full->to_string());
    }
);
