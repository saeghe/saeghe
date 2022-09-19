<?php

namespace Saeghe\Saeghe\Commands\Help;

use Saeghe\Cli\IO\Write;

function run()
{
    $content = <<<EOD
usage: saeghe [command] [options...]

These are common Saeghe commands used in various situations:

Initializing the project:
    init {--packages-directory=}
                    Initializes the project. This command adds required files and directories. You can pass a
                    `packages-directory` as an option. If passed, then your packages will be added under the given
                    directory instead of the default `Packages` directory.

Build project files:
    build {--environment=}
                    Builds project and adds built files to environment's build directory under the `build` directory. By
                    default environment will be `development`. You can pass the `environment` option as `production` when
                    you want to build the production environment.

Flush built files:
    flush
                    If you need to delete any built files, running this command will give you a fresh `builds` directory.

Watch for development:
    watch
                    By running this command, Saeghe builds your file while you are doing your development. This command
                    always builds files under the `development` environment.

Add a package to the project:
    add --package= {--version=}
                    Adds the given package to your project. This command needs a required `package` option. You can pass an
                    optional `version` option, then Saeghe will add the given version of the given package, otherwise, it
                    adds the latest released version on the package. The package’s source code will be added under your package’s
                    directory, the package's path and installed version will be added to your `saeghe.config.json` file and
                    its metadata will be added to the `saeghe.config-lock.json` file.

Remove package from the project:
    remove --package=
                    Removes the given package from your project. This command needs a required `package` option. It deleted
                    given package's source files from your packages directory and also removes the package from
                    `saeghe.config.json` and its metadata from `saeghe.config-lock.json`.

Install packages on a cloned project:
    install
                    When you clone your project, you don't have your packages source code (unless you didn't add your
                    packages directory to the .gitignore file, which is not recommended) After the clone, by running install
                    command, Saeghe will download added packages to your packages directory.

Update installed packages:
    update --package= {--version=}
                    If you need to get the latest version of an added package, you can run the update command. This command
                    needs a required `package` option. You can also path an optional `version` option, if passed, then
                    Saeghe will download the exact version number, if not passed, it downloads the latest available version.
                    
  
EOD;

    Write\line($content);
}
