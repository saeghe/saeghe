<?php

namespace Tests\System\InstallCommand\InstallWhenGithubTokenIsSetTest;

use Saeghe\FileManager\FileType\Json;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

test(
    title: 'it should not show error message when the credential file is not exists and GITHUB_TOKEN is set',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe install --project=TestRequirements/Fixtures/EmptyProject');

        $packages = root() . 'TestRequirements/Fixtures/EmptyProject/Packages/';
        $expected = <<<EOD
\e[39mInstalling packages...
\e[39mSetting env credential...
\e[39mLoading configs...
\e[39mDownloading packages...
\e[39mDownloading package git@github.com:saeghe/released-package.git to {$packages}saeghe/released-package
\e[39mDownloading package git@github.com:saeghe/complex-package to {$packages}saeghe/complex-package
\e[39mDownloading package git@github.com:saeghe/simple-package.git to {$packages}saeghe/simple-package
\e[92mPackages has been installed successfully.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject');
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages'));
        $credential = Json\to_array(root() . 'credentials.json');
        github_token($credential[GITHUB_DOMAIN]['token']);
        rename(root() . 'credentials.json', root() . 'credentials.json.back');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
        rename(root() . 'credentials.json.back', root() . 'credentials.json');
    },
);
