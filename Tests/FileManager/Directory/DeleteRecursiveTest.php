<?php

namespace Tests\FileManager\Directory\DeleteRecursiveTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;

test(
    title: 'it should delete directory when it is empty',
    case: function (Path $directory) {
        assert_true(delete_recursive($directory->stringify()));
        assert_false(file_exists($directory->stringify()), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/DeleteRecursive');
        mkdir($directory->stringify());

        return $directory;
    }
);

test(
    title: 'it should delete directory recursively',
    case: function (Path $directory) {
        assert_true(delete_recursive($directory->stringify()));

        assert_true(false ===file_exists($directory->stringify()), 'delete_recursive is not working!');
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/DeleteRecursive');
        $sub_directory = $directory->append('SubDirectory');
        $another_sub_directory = $directory->append('SubDirectory/AnotherSubDirectory');
        mkdir($directory->stringify());
        mkdir($sub_directory->stringify());
        mkdir($another_sub_directory->stringify());
        file_put_contents($directory->append('FileInDirectory.php')->stringify(), '<?php');
        file_put_contents($sub_directory->append('FileInSubDirectory.txt')->stringify(), 'content');
        file_put_contents($another_sub_directory->append('FileInAnotherSubDirectory.json')->stringify(), '');

        return $directory;
    }
);
