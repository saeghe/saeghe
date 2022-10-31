<?php

namespace Tests\FileManager\Directory\DeleteRecursiveTest;

use Saeghe\Saeghe\Path;
use function Saeghe\FileManager\Directory\delete_recursive;

test(
    title: 'it should delete directory when it is empty',
    case: function (Path $directory) {
        assert(delete_recursive($directory->toString()));
        assert(! file_exists($directory->toString()), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Path::fromString(__DIR__ . '/../../PlayGround/DeleteRecursive');
        mkdir($directory->toString());

        return $directory;
    }
);

test(
    title: 'it should delete directory recursively',
    case: function (Path $directory) {
        assert(delete_recursive($directory->toString()));

        assert(! file_exists($directory->toString()), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Path::fromString(__DIR__ . '/../../PlayGround/DeleteRecursive');
        $subDirectory = $directory->append('SubDirectory');
        $anotherSubDirectory = $directory->append('SubDirectory/AnotherSubDirectory');
        mkdir($directory->toString());
        mkdir($subDirectory->toString());
        mkdir($anotherSubDirectory->toString());
        file_put_contents($directory->append('FileInDirectory.php')->toString(), '<?php');
        file_put_contents($subDirectory->append('FileInSubDirectory.txt')->toString(), 'content');
        file_put_contents($anotherSubDirectory->append('FileInAnotherSubDirectory.json')->toString(), '');

        return $directory;
    }
);
