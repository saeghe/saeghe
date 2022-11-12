<?php

namespace Tests\System\HelpCommandTest;

use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

$help_content = <<<'EOD'
usage: saeghe [-v | --version] [-h | --help] [--man]
           <command> [<args>]

These are common Saeghe commands used in various situations:

start a working area
    init                Initialize an empty project or reinitialize an existing one
    migrate             Migrate from a composer project

work with packages
    credential          Add credential for given provider 
    add                 Add a git repository as a package
    remove              Remove a git repository from packages
    update              Update the version of given package
    install             Installs package dependencies
    
work on an existing project
    build               Build the project
    watch               Watch file changes and build the project for each change
    flush               Flush files in build directory
EOD;

test(
    title: 'it should show help output',
    case: function () use ($help_content) {
        $output = shell_exec('php ' . root() . 'saeghe -h');

        assert_true(str_contains($output, $help_content), 'Help output is not what we want!' . $output);
    }
);

test(
    title: 'it should show help output when help option passed',
    case: function () use ($help_content) {
        $output = shell_exec('php ' . root() . 'saeghe --help');

        assert_true(str_contains($output, $help_content), 'Help output is not what we want!' . $output);
    }
);

test(
    title: 'it should show help output when no command passed',
    case: function () use ($help_content) {
        $output = shell_exec('php ' . root() . 'saeghe');

        assert_true(str_contains($output, $help_content), 'Help output is not what we want!' . $output);
    }
);
