<?php

namespace Tests\FileManager\Directory\DeleteRecursiveTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should delete directory when it is empty',
    case: function (Path $directory) {
        assert_true(delete_recursive($directory));
        assert_false(file_exists($directory), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/DeleteRecursive');
        mkdir($directory);

        return $directory;
    }
);

test(
    title: 'it should delete directory recursively',
    case: function (Path $directory) {
        assert_true(delete_recursive($directory));

        assert_true(false === file_exists($directory), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/DeleteRecursive');
        $sub_directory = $directory->append('SubDirectory');
        $another_sub_directory = $directory->append('SubDirectory/AnotherSubDirectory');
        mkdir($directory);
        mkdir($sub_directory);
        mkdir($another_sub_directory);
        file_put_contents($directory->append('FileInDirectory.php'), '<?php');
        file_put_contents($sub_directory->append('FileInSubDirectory.txt'), 'content');
        file_put_contents($another_sub_directory->append('FileInAnotherSubDirectory.json'), '');

        return $directory;
    }
);
