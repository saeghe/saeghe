<?php

namespace Tests\GitTest\GitHubTest;

use Saeghe\Saeghe\Path;
use function Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\Providers\GitHub\clone_to;
use function Saeghe\Saeghe\Providers\GitHub\download;
use function Saeghe\Saeghe\Providers\GitHub\extract_owner;
use function Saeghe\Saeghe\Providers\GitHub\extract_repo;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_commit_hash;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_version;
use function Saeghe\Saeghe\Providers\GitHub\find_version_hash;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use function Saeghe\Saeghe\Providers\GitHub\has_release;
use function Saeghe\Saeghe\Providers\GitHub\isSsh;

require_once __DIR__ . '/../../Source/Git/GitHub.php';

test(
    title: 'it should detect if url is ssh',
    case: function () {
        assert(isSsh('git@github.com:owner/repo'));
        assert(! isSsh('https://github.com/owner/repo'));
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
    title: 'it should detect if repository has release',
    case: function () {
        assert(has_release('saeghe', 'released-package'));
        assert(! has_release('saeghe', 'simple-package'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(__DIR__ . '/../../credentials.json'), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials['github.com']['token']);
    }
);

test(
    title: 'it should find latest version for released repository',
    case: function () {
        assert('v1.0.5' === find_latest_version('saeghe', 'released-package'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(__DIR__ . '/../../credentials.json'), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials['github.com']['token']);
    }
);

test(
    title: 'it should find version hash for released repository',
    case: function () {
        assert('9e9b796915596f7c5e0b91d2f9fa5f916a9b5cc8' === find_version_hash('saeghe', 'released-package', 'v1.0.3'));
        assert('5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === find_version_hash('saeghe', 'released-package', 'v1.0.5'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(__DIR__ . '/../../credentials.json'), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials['github.com']['token']);
    }
);

test(
    title: 'it should find latest commit hash for repository',
    case: function () {
        assert('85f94d8c34cb5678a5b37707479517654645c102' === find_latest_commit_hash('saeghe', 'simple-package'));
        assert('5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === find_latest_commit_hash('saeghe', 'released-package'));
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(__DIR__ . '/../../credentials.json'), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials['github.com']['token']);
    }
);

test(
    title: 'it should download given repository',
    case: function (Path $packagesDirectory) {
        assert(download($packagesDirectory->toString(), 'saeghe', 'released-package', 'v1.0.5'));
        // Assert latest changes on the latest commit
        assert(
            str_contains(
                file_get_contents($packagesDirectory->append('saeghe.config-lock.json')->toString()),
                '080478442a9ef1d19f5966edc9bf3c1eccca4848'
            )
        );
        assert(! file_exists($packagesDirectory->parent()->append('released-package.zip')->toString()));

        return $packagesDirectory;
    },
    before: function () {
        $credentials = json_decode(json: file_get_contents(__DIR__ . '/../../credentials.json'), associative: true, flags: JSON_THROW_ON_ERROR);
        github_token($credentials['github.com']['token']);
        $packagesDirectory = Path::fromString(__DIR__ . '/../PlayGround/downloads/package');
        mkdir($packagesDirectory->toString(), 0777, true);

        return $packagesDirectory;
    },
    after: function (Path $packagesDirectory) {
        flush($packagesDirectory->parent()->parent()->toString());
    }
);

test(
    title: 'it should clone given repository',
    case: function (Path $packagesDirectory) {
        assert(clone_to($packagesDirectory->toString(), 'saeghe', 'simple-package'));
        // Assert latest changes on the latest commit
        assert(
            str_contains(
                file_get_contents($packagesDirectory->append('entry-point')->toString()),
                'new ImaginaryClass();'
            )
        );

        return $packagesDirectory;
    },
    before: function () {
        $packagesDirectory = Path::fromString(__DIR__ . '/../PlayGround/downloads/package');
        mkdir($packagesDirectory->toString(), 0777, true);

        return $packagesDirectory;
    },
    after: function (Path $packagesDirectory) {
        flush($packagesDirectory->parent()->parent()->toString());
    }
);
