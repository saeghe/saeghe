<?php

namespace Tests\Git\GitHub\GetJsonTest;

use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Git\Exception\InvalidTokenException;
use function Saeghe\Saeghe\FileManager\Resolver\realpath;
use function Saeghe\Saeghe\Providers\GitHub\get_json;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

test(
    title: 'it should get json response from github api',
    case: function () {
        assert_true('Saeghe package manager' === get_json('repos/saeghe/saeghe')['description']);
    },
    before: function () {
        $credentials = Json\to_array(realpath(root() . 'credentials.json'));
        github_token($credentials[GITHUB_DOMAIN]['token']);
    }
);

test(
    title: 'it should throw exception when token is not valid',
    case: function () {
        try {
            get_json('repos/saeghe/saeghe');
            assert_false(true, 'It should not pass');
        } catch (InvalidTokenException $exception) {
            assert_true($exception->getMessage() === 'GitHub token is not valid.');
        }
    },
    before: function () {
        github_token('');
    }
);
