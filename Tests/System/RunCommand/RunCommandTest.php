<?php

namespace Tests\System\RunCommand\RunCommandTest;

use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should run the given entry point on the given package',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe run https://github.com/saeghe/chuck-norris.git');

        assert_true(str_contains($output, 'Chuck Norris'));
    }
);

test(
    title: 'it should show error message when the entry point is not defined',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe run https://github.com/saeghe/chuck-norris.git not-exists.php');

        $expected = <<<EOD
\e[91mEntry point not-exists.php is not defined in the package.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    }
);
