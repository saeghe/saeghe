<?php

namespace Tests\HelpCommandTest;

$helpContent = <<<EOD
usage: saeghe [command] [options...]

These are common Saeghe commands used in various situations:

Initializing the project:
    init {--packages-directory=}
                    Initializes the project. This command adds required files and directories. You can pass a 
                    `packages-directory` as an option. If passed, then your packages will be added under the given 
                    directory instead of default `Packages` directory.

Build project files:
    build {--environment=}
                    Builds project and adds built files to environment's build directory under `build` directory. By 
                    default environment will be `development`. You can pass `environment` option as `production`, when 
                    you want to build for production environment.

Flush built files:
    flush
                    If you need to delete any built files, running this command will gives you a fresh `builds` 
                    directory.

Watch for development:
    watch
                    By running this command, Saeghe builds your file while you are doing your development. This command 
                    always builds files under `development` environment. 

Add package to the project:
    add --package= {--version=}
                    Adds given package to your project. This command needs a required `package` option. You can pass an 
                    optional `version` option, then Saeghe will add given version of the given package, otherwise, it 
                    adds latest released version on the package. Package's source code will be added under your packages
                    directory, package's path and installed version will be added to your `saeghe.config.json` file and 
                    its metadata will be added to `saeghe.config-lock.json` file.

Remove package from the project:
    remove --package=
                    Removes given package from your project. This command needs a required `package` option. It deleted 
                    given package's source files from your packages directory and also removes the package from 
                    `saeghe.config.json` and its metadata from `saeghe.config-lock.json`.

Install packages on a cloned project:
    install
                    When you clone your project, you don't have your packages source code (unless you didn't add your 
                    packages directory to the .gitignore file, which is not recommended) After clone, by running install
                    command, Saeghe will download added packages to your packages directory.
                    
Update installed packages:
    update --package= {--version=}
                    If you need to get the latest version of an added package, you can run update command. This command
                    needs a required `package` option. You can also path a optional `version` option, if passed, then
                    Saeghe will download exact version number, if not passed, it downloads latest available version.

EOD;


test(
    'it should return desired help output',
    case: function () use ($helpContent) {
        $output = shell_exec($_SERVER['PWD'] . '/saeghe --command=help');

        assert(str_contains($output, $helpContent), 'Help output is not what we want!' . $output);
    }
);
