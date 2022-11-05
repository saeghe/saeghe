<?php

namespace Tests\FileManager\Directory\CleanTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\clean;

test(
    title: 'it should clean directory',
    case: function (Address $directory) {
        clean($directory->to_string());

        assert_true(file_exists($directory->to_string()), 'clean is not working!');
        assert_true(
            scandir($directory->to_string()) === ['.', '..'],
            'clean is not working and there are some items in the directory!'
        );

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Clean');
        $subDirectory = $directory->append('SubDirectory');
        mkdir($directory->to_string());
        mkdir($subDirectory->to_string());
        file_put_contents($directory->append('FileInDirectory.php')->to_string(), '<?php');

        return $directory;
    },
    after: function (Address $directory) {
        rmdir($directory->to_string());
    }
);
