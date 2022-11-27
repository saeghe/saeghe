<?php

namespace Tests\System\InstallCommand\InstallWhenCredentialNotExistsTest;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should show error message when the credential file is not exists and there is no GITHUB_TOKEN',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe install --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mInstalling packages...
\e[39mSetting env credential...
\e[91mThere is no credential file. Please use the `credential` command to add your token.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject');
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages'));
        rename(root() . 'credentials.json', root() . 'credentials.json.back');
        github_token('');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
        rename(root() . 'credentials.json.back', root() . 'credentials.json');
    },
);
