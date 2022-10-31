<?php

namespace Saeghe\Saeghe\Commands\Help;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Project;

function run(Project $project)
{
    $content = <<<EOD
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

    Write\line($content);
}
