<?php

namespace Tests\GitTest\GitHubTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\Providers\GitHub\clone_to;
use function Saeghe\Saeghe\Providers\GitHub\download;
use function Saeghe\Saeghe\Providers\GitHub\extract_owner;
use function Saeghe\Saeghe\Providers\GitHub\extract_repo;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_commit_hash;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_version;
use function Saeghe\Saeghe\Providers\GitHub\find_version_hash;
use function Saeghe\Saeghe\Providers\GitHub\get_json;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use function Saeghe\Saeghe\Providers\GitHub\has_release;
use function Saeghe\Saeghe\Providers\GitHub\is_ssh;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should detect if url is ssh',
    case: function () {
        assert(is_ssh('git@github.com:owner/repo'));
        assert(! is_ssh('https://github.com/owner/repo'));
    }
);

test(
    title: 'it should extract owner from url',
    case: function () {
        assert('saeghe' === extract_owner('git@github.com:saeghe/repo'));
        assert('saeghe' === extract_owner('git@github.com:saeghe/repo.git'));
        assert('saeghe' === extract_owner('https://github.com/saeghe/repo'));
    }
);

test(
    title: 'it should extract repo from url',
    case: function () {
        assert('cli' === extract_repo('git@github.com:saeghe/cli'));
        assert('cli' === extract_repo('git@github.com:saeghe/cli.git'));
        assert('test-runner' === extract_repo('https://github.com/saeghe/test-runner'));
    }
);

test(
    title: 'it should get and set github token',
    case: function () {
        putenv("GITHUB_TOKEN=FIRST_TOKEN");
        assert('FIRST_TOKEN' === github_token());

        github_token('set new token');
        assert(getenv('GITHUB_TOKEN', true) === 'set new token');

        $token = 'set another token';
        assert(github_token($token) === $token);
        assert(github_token() === $token);
    }
);

test(
    title: 'it should get json response from github api',
    case: function () {
        assert('Saeghe package manager' === get_json('repos/saeghe/saeghe')['description']);
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(realpath(root() . 'credentials.json')), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should detect if repository has release',
    case: function () {
        assert(has_release('saeghe', 'released-package'));
        assert(! has_release('saeghe', 'simple-package'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(realpath(root() . 'credentials.json')), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should find latest version for released repository',
    case: function () {
        assert('v1.0.5' === find_latest_version('saeghe', 'released-package'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(realpath(root() . 'credentials.json')), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should find version hash for released repository',
    case: function () {
        assert('9e9b796915596f7c5e0b91d2f9fa5f916a9b5cc8' === find_version_hash('saeghe', 'released-package', 'v1.0.3'));
        assert('5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === find_version_hash('saeghe', 'released-package', 'v1.0.5'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(realpath(root() . 'credentials.json')), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should find latest commit hash for repository',
    case: function () {
        assert('85f94d8c34cb5678a5b37707479517654645c102' === find_latest_commit_hash('saeghe', 'simple-package'));
        assert('5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === find_latest_commit_hash('saeghe', 'released-package'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(realpath(root() . 'credentials.json')), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should download given repository',
    case: function (Address $packages_directory) {
        assert(download($packages_directory->to_string(), 'saeghe', 'released-package', 'v1.0.5'));
        // Assert latest changes on the latest commit
        assert(
            str_contains(
                file_get_contents($packages_directory->append('saeghe.config-lock.json')->to_string()),
                '080478442a9ef1d19f5966edc9bf3c1eccca4848'
            )
        );
        assert(! file_exists($packages_directory->parent()->append('released-package.zip')->to_string()));

        return $packages_directory;
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(realpath(root() . 'credentials.json')), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials[GITHUB_DOMAIN]['token']);
        $packages_directory = Address::from_string(root() . 'Tests/PlayGround/downloads/package');
        mkdir($packages_directory->to_string(), 0777, true);

        return $packages_directory;
    },
    after: function (Address $packages_directory) {
        flush($packages_directory->parent()->parent()->to_string());
    }
);

test(
    title: 'it should clone given repository',
    case: function (Address $packages_directory) {
        assert(clone_to($packages_directory->to_string(), 'saeghe', 'simple-package'));
        // Assert latest changes on the latest commit
        assert(
            str_contains(
                file_get_contents($packages_directory->append('entry-point')->to_string()),
                'new ImaginaryClass();'
            )
        );

        return $packages_directory;
    },
    before: function () {
        $packages_directory = Address::from_string(root() . 'Tests/PlayGround/downloads/package');
        mkdir($packages_directory->to_string(), 0777, true);

        return $packages_directory;
    },
    after: function (Address $packages_directory) {
        flush($packages_directory->parent()->parent()->to_string());
    }
);
