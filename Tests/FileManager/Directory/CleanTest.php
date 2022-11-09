<?php

namespace Tests\FileManager\Directory\CleanTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\clean;

test(
    title: 'it should clean directory',
    case: function (Path $directory) {
        clean($directory->stringify());

        assert_true(file_exists($directory->stringify()), 'clean is not working!');
        assert_true(
            scandir($directory->stringify()) === ['.', '..'],
            'clean is not working and there are some items in the directory!'
        );

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Clean');
        $subDirectory = $directory->append('SubDirectory');
        mkdir($directory->stringify());
        mkdir($subDirectory->stringify());
        file_put_contents($directory->append('FileInDirectory.php')->stringify(), '<?php');

        return $directory;
    },
    after: function (Path $directory) {
        rmdir($directory->stringify());
    }
);
