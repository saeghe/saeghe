<?php

namespace Tests\FileManager\Directory\FlushTest;

use Saeghe\Saeghe\Path;
use function Saeghe\FileManager\Directory\flush;

test(
    title: 'it should flush directory',
    case: function (Path $directory) {
        flush($directory->toString());

        assert(file_exists($directory->toString()), 'flush is not working!');
        assert(
            scandir($directory->toString()) === ['.', '..'],
            'flush is not working and there are some items in the directory!'
        );

        return $directory;
    },
    before: function () {
        $directory = Path::fromString(__DIR__ . '/Temp');
        $subDirectory = $directory->append('SubDirectory');
        mkdir($directory->toString());
        mkdir($subDirectory->toString());
        file_put_contents($directory->append('FileInDirectory.php')->toString(), '<?php');

        return $directory;
    },
    after: function (Path $directory) {
        rmdir($directory->toString());
    }
);
