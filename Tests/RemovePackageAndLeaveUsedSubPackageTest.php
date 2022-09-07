<?php

namespace Tests\RemovePackageAndLeaveUsedSubPackageTest;

test(
    title: 'it should remove the package but leave used subpackage',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=remove --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/complex-package.git");

        assertDesiredDataInPackagesDirectory('Package has not been deleted from Packages directory!' . $output);
        assertBuildJsonIsClean('Packages has not been deleted from build json file!' . $output);
        assertBuildLockIsClean('Packages has not been deleted from build lock file!' . $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/simple-package.git");
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/complex-package.git");
    },
    after: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=remove --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/simple-package.git");
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    }
);

function assertDesiredDataInPackagesDirectory($message)
{
    clearstatcache();
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/simple-package')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/complex-package')
    ,
        $message
    );
}

function assertBuildJsonIsClean($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/simple-package.git'])
        && ! isset($config['packages']['git@github.com:saeghe/complex-package.git']),
        $message
    );
}

function assertBuildLockIsClean($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(isset($config['packages']['git@github.com:saeghe/simple-package.git']), $message);
}
