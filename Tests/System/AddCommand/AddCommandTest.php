<?php

namespace Tests\System\AddCommand\AddCommandTest;

use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\TestRunner\Assertions\File;
use function Saeghe\Cli\IO\Write\assert_error;
use function Saeghe\Cli\IO\Write\assert_success;
use function Saeghe\Saeghe\FileManager\Directory\clean;
use function Saeghe\Saeghe\FileManager\Resolver\realpath;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

test(
    title: 'it should show error message when project is not initialized',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');
        assert_error('Project is not initialized. Please try to initialize using the init command.', $output);
    }
);

test(
    title: 'it should return error when given url is not a Saeghe package',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add https://github.com/symfony/thanks.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_error('Given https://github.com/symfony/thanks.git URL is not a Saeghe package.', $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should show error message when there is no credential files and there is no GITHUB_TOKEN',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_error('There is no credential file. Please use the `credential` command to add your token.', $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        rename(root() . 'credentials.json', root() . 'credentials.json.back');
        github_token('');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
        rename(root() . 'credentials.json.back', root() . 'credentials.json');
    }
);

test(
    title: 'it should not show error message when there is no credential files but GITHUB_TOKEN is set',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_success('Package git@github.com:saeghe/simple-package.git has been added successfully.', $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        $credential = Json\to_array(root() . 'credentials.json');
        github_token($credential[GITHUB_DOMAIN]['token']);
        rename(root() . 'credentials.json', root() . 'credentials.json.back');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
        rename(root() . 'credentials.json.back', root() . 'credentials.json');
    }
);

test(
    title: 'it should show error message when token is invalid',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_error(
            'The GitHub token is not valid. Either, you didn\'t set one yet, or it is not valid. Please use the `credential` command to set a valid token.',
            $output
        );
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        rename(root() . 'credentials.json', root() . 'credentials.json.back');
        shell_exec('php ' . root() . 'saeghe credential github.vom not-valid');
        github_token('');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
        rename(root() . 'credentials.json.back', root() . 'credentials.json');
    }
);

test(
    title: 'it should add package to the project',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_success('Package git@github.com:saeghe/simple-package.git has been added successfully.', $output);
        assert_config_file_created_for_simple_project('Config file is not created!' . $output);
        assert_simple_package_added_to_config('Simple Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_simple_package_cloned('Simple package does not cloned!' . $output);
        assert_meta_has_desired_data('Meta data is not what we want.' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should add package to the project without trailing .git',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package --project=TestRequirements/Fixtures/EmptyProject');

        assert_simple_package_cloned('Simple package does not cloned!' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should use same repo with git and https urls',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add https://github.com/saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_error('Package https://github.com/saeghe/simple-package.git is already exists', $output);
        $config = Json\to_array(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json');
        $meta = Json\to_array(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json');
        assert_true(count($config['packages']) === 1);
        assert_true(count($meta['packages']) === 1);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_config_file_created_for_simple_project($message)
{
    File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages'), $message);
}

function assert_simple_package_cloned($message)
{
    assert_true((
            File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/Saeghe/simple-package'))
            && File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/Saeghe/simple-package/saeghe.config.json'))
            && File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/Saeghe/simple-package/README.md'))
        ),
        $message
    );
}

function assert_simple_package_added_to_config($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true((
            isset($config['packages']['git@github.com:saeghe/simple-package.git'])
            && 'development' === $config['packages']['git@github.com:saeghe/simple-package.git']
        ),
        $message
    );
}

function assert_meta_has_desired_data($message)
{
    $meta = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true((
            isset($meta['packages']['git@github.com:saeghe/simple-package.git'])
            && 'development' === $meta['packages']['git@github.com:saeghe/simple-package.git']['version']
            && 'saeghe' === $meta['packages']['git@github.com:saeghe/simple-package.git']['owner']
            && 'simple-package' === $meta['packages']['git@github.com:saeghe/simple-package.git']['repo']
            && '85f94d8c34cb5678a5b37707479517654645c102' === $meta['packages']['git@github.com:saeghe/simple-package.git']['hash']
        ),
        $message
    );
}
