<?php

namespace Tests\FileManager\Directory\FlushTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\Directory\flush;

test(
    title: 'it should flush directory',
    case: function (Address $directory) {
        flush($directory->to_string());

        assert(file_exists($directory->to_string()), 'flush is not working!');
        assert(
            scandir($directory->to_string()) === ['.', '..'],
            'flush is not working and there are some items in the directory!'
        );

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Flush');
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
