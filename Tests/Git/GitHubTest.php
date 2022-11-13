<?php

namespace Tests\GitTest\GitHubTest;

use Saeghe\FileManager\Path;
use Saeghe\FileManager\FileType\Json;
use function Saeghe\Saeghe\Providers\GitHub\clone_to;
use function Saeghe\Saeghe\Providers\GitHub\download;
use function Saeghe\Saeghe\Providers\GitHub\extract_owner;
use function Saeghe\Saeghe\Providers\GitHub\extract_repo;
use function Saeghe\Saeghe\Providers\GitHub\file_exists;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_commit_hash;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_version;
use function Saeghe\Saeghe\Providers\GitHub\find_version_hash;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use function Saeghe\Saeghe\Providers\GitHub\has_release;
use function Saeghe\Saeghe\Providers\GitHub\is_ssh;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

test(
    title: 'it should detect if url is ssh',
    case: function () {
        assert_true(is_ssh('git@github.com:owner/repo'));
        assert_false(is_ssh('https://github.com/owner/repo'));
    }
);

test(
    title: 'it should extract owner from url',
    case: function () {
        assert_true('saeghe' === extract_owner('git@github.com:saeghe/repo'));
        assert_true('saeghe' === extract_owner('git@github.com:saeghe/repo.git'));
        assert_true('saeghe' === extract_owner('https://github.com/saeghe/repo'));
    }
);

test(
    title: 'it should extract repo from url',
    case: function () {
        assert_true('cli' === extract_repo('git@github.com:saeghe/cli'));
        assert_true('cli' === extract_repo('git@github.com:saeghe/cli.git'));
        assert_true('test-runner' === extract_repo('https://github.com/saeghe/test-runner'));
    }
);

test(
    title: 'it should get and set github token',
    case: function () {
        putenv("GITHUB_TOKEN=FIRST_TOKEN");
        assert_true('FIRST_TOKEN' === github_token());

        github_token('set new token');
        assert_true(getenv('GITHUB_TOKEN', true) === 'set new token');

        $token = 'set another token';
        assert_true(github_token($token) === $token);
        assert_true(github_token() === $token);
    }
);

test(
    title: 'it should detect if repository has release',
    case: function () {
        assert_true(has_release('saeghe', 'released-package'));
        assert_false(has_release('saeghe', 'simple-package'));
    },
    before: function () {
        $credentials = Json\to_array(realpath(root() . 'credentials.json'));
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should find latest version for released repository',
    case: function () {
        assert_true('v1.0.6' === find_latest_version('saeghe', 'released-package'));
    },
    before: function () {
        $credentials = Json\to_array(realpath(root() . 'credentials.json'));
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should find version hash for released repository',
    case: function () {
        assert_true('9e9b796915596f7c5e0b91d2f9fa5f916a9b5cc8' === find_version_hash('saeghe', 'released-package', 'v1.0.3'));
        assert_true('5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === find_version_hash('saeghe', 'released-package', 'v1.0.5'));
        assert_true('5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === find_version_hash('saeghe', 'released-package', 'v1.0.6'));
    },
    before: function () {
        $credentials = Json\to_array(realpath(root() . 'credentials.json'));
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should find latest commit hash for repository',
    case: function () {
        assert_true('85f94d8c34cb5678a5b37707479517654645c102' === find_latest_commit_hash('saeghe', 'simple-package'));
        assert_true('5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === find_latest_commit_hash('saeghe', 'released-package'));
    },
    before: function () {
        $credentials = Json\to_array(realpath(root() . 'credentials.json'));
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should download given repository',
    case: function (Path $packages_directory) {
        assert_true(download($packages_directory, 'saeghe', 'released-package', 'v1.0.5'));
        // Assert latest changes on the latest commit
        assert_true(true ===
            str_contains(
                file_get_contents($packages_directory->append('saeghe.config-lock.json')),
                '080478442a9ef1d19f5966edc9bf3c1eccca4848'
            )
        );
        assert_false(\file_exists($packages_directory->parent()->append('released-package.zip')));

        return $packages_directory;
    },
    before: function () {
        $credentials = Json\to_array(realpath(root() . 'credentials.json'));
        github_token($credentials[GITHUB_DOMAIN]['token']);
        $packages_directory = Path::from_string(root() . 'Tests/PlayGround/downloads/package');
        mkdir($packages_directory, 0777, true);

        return $packages_directory;
    },
    after: function (Path $packages_directory) {
        $packages_directory->parent()->delete_recursive();
    }
);

test(
    title: 'it should clone given repository',
    case: function (Path $packages_directory) {
        assert_true(clone_to($packages_directory, 'saeghe', 'simple-package'));
        // Assert latest changes on the latest commit
        assert_true(true ===
            str_contains(
                file_get_contents($packages_directory->append('entry-point')),
                'new ImaginaryClass();'
            )
        );

        return $packages_directory;
    },
    before: function () {
        $packages_directory = Path::from_string(root() . 'Tests/PlayGround/downloads/package');
        mkdir($packages_directory, 0777, true);

        return $packages_directory;
    },
    after: function (Path $packages_directory) {
        $packages_directory->parent()->delete_recursive();
    }
);

test(
    title: 'it should check if file exists on the git repository',
    case: function () {
        assert_false(file_exists('saeghe', 'saeghe', 'e71c51fa95f9e13fd854958ea97629a9172b746c', 'saeghe'));
        assert_true(file_exists('saeghe', 'saeghe', 'e71c51fa95f9e13fd854958ea97629a9172b746c', 'LICENSE'));
    }
);
