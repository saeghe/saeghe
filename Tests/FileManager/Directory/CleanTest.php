<?php

namespace Tests\FileManager\Directory\CleanTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\clean;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should clean directory',
    case: function (Path $directory) {
        clean($directory);

        assert_true(file_exists($directory), 'clean is not working!');
        assert_true(
            scandir($directory) === ['.', '..'],
            'clean is not working and there are some items in the directory!'
        );

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Clean');
        $subDirectory = $directory->append('SubDirectory');
        mkdir($directory);
        mkdir($subDirectory);
        file_put_contents($directory->append('FileInDirectory.php'), '<?php');

        return $directory;
    },
    after: function (Path $directory) {
        rmdir($directory);
    }
);
