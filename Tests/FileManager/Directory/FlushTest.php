<?php

namespace Tests\FileManager\Directory\FlushTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\Directory\flush;

test(
    title: 'it should flush directory',
    case: function (Address $directory) {
        flush($directory->toString());

        assert(file_exists($directory->toString()), 'flush is not working!');
        assert(
            scandir($directory->toString()) === ['.', '..'],
            'flush is not working and there are some items in the directory!'
        );

        return $directory;
    },
    before: function () {
        $directory = Address::fromString(__DIR__ . '/../../PlayGround/Flush');
        $subDirectory = $directory->append('SubDirectory');
        mkdir($directory->toString());
        mkdir($subDirectory->toString());
        file_put_contents($directory->append('FileInDirectory.php')->toString(), '<?php');

        return $directory;
    },
    after: function (Address $directory) {
        rmdir($directory->toString());
    }
);
