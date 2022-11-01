<?php

namespace Tests\FileManager\Directory\DeleteRecursiveTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;

test(
    title: 'it should delete directory when it is empty',
    case: function (Address $directory) {
        assert(delete_recursive($directory->to_string()));
        assert(! file_exists($directory->to_string()), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/DeleteRecursive');
        mkdir($directory->to_string());

        return $directory;
    }
);

test(
    title: 'it should delete directory recursively',
    case: function (Address $directory) {
        assert(delete_recursive($directory->to_string()));

        assert(! file_exists($directory->to_string()), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/DeleteRecursive');
        $sub_directory = $directory->append('SubDirectory');
        $another_sub_directory = $directory->append('SubDirectory/AnotherSubDirectory');
        mkdir($directory->to_string());
        mkdir($sub_directory->to_string());
        mkdir($another_sub_directory->to_string());
        file_put_contents($directory->append('FileInDirectory.php')->to_string(), '<?php');
        file_put_contents($sub_directory->append('FileInSubDirectory.txt')->to_string(), 'content');
        file_put_contents($another_sub_directory->append('FileInAnotherSubDirectory.json')->to_string(), '');

        return $directory;
    }
);
