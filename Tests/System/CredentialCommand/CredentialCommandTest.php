<?php

namespace Tests\System\CredentialCommand\CredentialCommandTest;

use Saeghe\Cli\IO\Write;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should set credential for github.com',
    case: function () {
        $token = 'a_token';
        $output = shell_exec($_SERVER['PWD'] . "/saeghe credential github.com $token");

        Write\assert_success('Credential for github.com has been set successfully.', $output);

        assert(
            ['github.com' => ['token' => $token]]
            ===
            json_decode(file_get_contents($_SERVER['PWD'] . '/credentials.json'), true, JSON_THROW_ON_ERROR),
            'Credential content is not set properly!'
        );
    },
    before: function () {
        if (file_exists($_SERVER['PWD'] . '/credentials.json')) {
            shell_exec('mv ' . $_SERVER['PWD'] . '/credentials.json ' . $_SERVER['PWD'] . '/credentials.json.back');
        }
    },
    after: function () {
        delete($_SERVER['PWD'] . '/credentials.json');
        if (file_exists($_SERVER['PWD'] . '/credentials.json.back')) {
            shell_exec('mv ' . $_SERVER['PWD'] . '/credentials.json.back ' . $_SERVER['PWD'] . '/credentials.json');
        }
    },
);

test(
    title: 'it should add credential for github.com',
    case: function () {
        $token = 'a_token';
        $output = shell_exec($_SERVER['PWD'] . "/saeghe credential github.com $token");

        Write\assert_success('Credential for github.com has been set successfully.', $output);

        assert(
            ['gitlab.com' => ['token' => 'gitlab-token'], 'github.com' => ['token' => $token]]
            ===
            json_decode(file_get_contents($_SERVER['PWD'] . '/credentials.json'), true, JSON_THROW_ON_ERROR),
            'Credential content is not set properly!'
        );
    },
    before: function () {
        if (file_exists($_SERVER['PWD'] . '/credentials.json')) {
            shell_exec('mv ' . $_SERVER['PWD'] . '/credentials.json ' . $_SERVER['PWD'] . '/credentials.json.back');
        }

        $credential = fopen($_SERVER['PWD'] . '/credentials.json', "w");
        fwrite($credential, json_encode(['gitlab.com' => ['token' => 'gitlab-token']], JSON_PRETTY_PRINT));
        fclose($credential);
    },
    after: function () {
        delete($_SERVER['PWD'] . '/credentials.json');
        if (file_exists($_SERVER['PWD'] . '/credentials.json.back')) {
            shell_exec('mv ' . $_SERVER['PWD'] . '/credentials.json.back ' . $_SERVER['PWD'] . '/credentials.json');
        }
    },
);
