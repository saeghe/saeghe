<?php

namespace Tests\InitializeTest;

$initialContent = <<<EOD
{
    "packages": []
}

EOD;

test(
    title: 'it makes a new default config file',
    case: function () use ($initialContent) {
        $buildConfig = $_SERVER['PWD'] . '/build.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe");

        assert(file_exists($buildConfig), 'Config file does not exists: ' . $output);

        assert($initialContent === file_get_contents($buildConfig),
            'Config file content is not correct after running initialize!'
        );

        return $buildConfig;
    },
    after: function ($buildConfig) {
        shell_exec('rm -f ' . $buildConfig);
    }
);

test(
    title: 'it makes a new config file with given filename',
    case: function ($buildConfig, $configPath) use ($initialContent) {
        $output = shell_exec("{$_SERVER['PWD']}/saeghe --config=$buildConfig");

        assert(file_exists($configPath), 'Custom config file does not exists: ' . $output);

        assert($initialContent === file_get_contents($_SERVER['PWD'] . '/' . $buildConfig),
            'Custom config file content is not correct after running initialize!'
        );

        return $configPath;
    },
    before: function () {
        $buildConfig = 'build-config.json';
        $configPath = $_SERVER['PWD'] . '/' . $buildConfig;
        // Make sure file does not exist
        shell_exec('rm -f ' . $configPath);

        return compact('buildConfig', 'configPath');
    },
    after: function ($configPath) {
        shell_exec('rm -f ' . $configPath);
    }
);
