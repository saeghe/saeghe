<?php

namespace Saeghe\Saeghe\Commands\Install;

use function Saeghe\Cli\IO\Write\success;

function run()
{
    global $meta;
    global $packagesDirectory;

    foreach ($meta['packages'] as $package => $meta) {
        install($meta, $packagesDirectory);
    }

    success('Packages has been installed successfully.');
}

function install($meta, $packagesDirectory)
{
    if (git_has_release($meta['owner'], $meta['repo'])) {
        git_download($packagesDirectory, $meta['owner'], $meta['repo'], $meta['version']);
    } else {
        git_clone($packagesDirectory, $meta['owner'], $meta['repo']);
    }
}
