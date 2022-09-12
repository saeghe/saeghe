<?php

namespace Saeghe\Saeghe\Commands\Add;

use ZipArchive;
use function Saeghe\Cli\IO\Read\argument;

function run()
{
    global $projectRoot;
    global $packagesDirectory;

    $package = argument('package');
    $version = argument('version');

    $packageMeta = add($packagesDirectory, $package, $version);
    find_or_create_config_file($projectRoot, $package, $packageMeta);
}

function add($packagesDirectory, $package, $version)
{
    global $projectRoot;

    $packageMeta = get_meta_from_package($package);

    if (package_has_release($packageMeta)) {
        $packageMeta = download_package($packagesDirectory, $version, $packageMeta);
    } else {
        $packageMeta = clone_package($packagesDirectory, $version, $packageMeta);
    }

    find_or_create_meta_file($projectRoot, $package, $packageMeta);

    $packagePath = $packagesDirectory . '/' . $packageMeta['owner'] . '/' . $packageMeta['repo'] . '/';
    $packageConfigPath = $packagePath . DEFAULT_CONFIG_FILENAME;
    if (file_exists($packageConfigPath)) {
        $packageConfig = json_decode(json: file_get_contents($packageConfigPath), associative: true, flags: JSON_THROW_ON_ERROR);
        foreach ($packageConfig['packages'] as $subPackage => $version) {
            add($packagesDirectory, $subPackage, $version);
        }
    }

    return $packageMeta;
}

function find_or_create_config_file($projectDirectory, $package, $meta)
{
    $configFile = $projectDirectory . DEFAULT_CONFIG_FILENAME;

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $config = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $config['packages'][$package] = $meta['version'];

    file_put_contents($projectDirectory . DEFAULT_CONFIG_FILENAME, json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
}

function find_or_create_meta_file($projectDirectory, $package, $packageMeta)
{
    $configFile = $projectDirectory . 'saeghe.config-lock.json';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $meta = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $meta['packages'][$package] = [
        'version' => $packageMeta['version'],
        'hash' => trim($packageMeta['hash']),
        'owner' => trim($packageMeta['owner']),
        'repo' => trim($packageMeta['repo']),
    ];
    file_put_contents($projectDirectory . 'saeghe.config-lock.json', json_encode($meta, JSON_PRETTY_PRINT) . PHP_EOL);
}

function clone_package($packageDirectory, $version, $meta)
{
    $destination = "$packageDirectory{$meta['owner']}/{$meta['repo']}";

    shell_exec("rm -fR $destination");
    shell_exec("git clone git@github.com:{$meta['owner']}/{$meta['repo']}.git $destination");

    $meta['hash'] = shell_exec("git --git-dir=$destination/.git rev-parse HEAD");
    $meta['version'] = 'development';

    return $meta;
}

function package_has_release($meta)
{
    global $credentials;

    $output = shell_exec('curl -H "Accept: application/vnd.github+json" -H "Authorization: Bearer ' . $credentials['github.com']['token'] . '" ' .  "https://api.github.com/repos/{$meta['owner']}/{$meta['repo']}/releases/latest | grep 'tag_name'");

    return ! is_null($output);
}

function download_package($packageDirectory, $version, $meta)
{
    global $credentials;

    if (is_null($version)) {
        $output = shell_exec('curl -H "Accept: application/vnd.github+json" -H "Authorization: Bearer ' . $credentials['github.com']['token'] . '" ' .  "https://api.github.com/repos/{$meta['owner']}/{$meta['repo']}/releases/latest | grep 'tag_name'");
        $version = str_replace('"tag_name": "', '', $output);
        $version = trim(explode('"', $version)[0]);
    }


    $ownerDirectory = $packageDirectory . $meta['owner'] . '/';

    if (! file_exists($ownerDirectory)) {
        mkdir($ownerDirectory);
    }

    $zipFile = $ownerDirectory . $meta['repo'] . '.zip';

    $token = $credentials['github.com']['token'];
    $fp = fopen ($zipFile, 'w+');
    $ch = curl_init("https://github.com/{$meta['owner']}/{$meta['repo']}/zipball/$version");
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    $zip = new ZipArchive;
    $res = $zip->open($zipFile);

    if ($res === TRUE) {
        $zip->extractTo($ownerDirectory);
        $zip->close();
    } else {
        var_dump($res);
    }

    shell_exec('rm -f ' . $zipFile);

    $files = scandir($ownerDirectory);

    $directory = array_reduce($files, function ($carry, $filename) use ($meta) {
        return str_starts_with($filename, "{$meta['owner']}-{$meta['repo']}-") ? $filename : $carry;
    });

    rename($ownerDirectory . $directory, $ownerDirectory . $meta['repo']);

    $meta['hash'] = str_replace("{$meta['owner']}-{$meta['repo']}-", '', $directory);
    $meta['version'] = $version;

    return $meta;
}

function get_meta_from_package($package)
{
    if (str_starts_with($package, 'git@')) {
        $ownerAndRepo = str_replace('git@github.com:', '', $package);
    } else {
        $ownerAndRepo = str_replace('https://github.com/', '', $package);
    }

    if (str_ends_with($ownerAndRepo, '.git')) {
        $ownerAndRepo = substr_replace($ownerAndRepo, '', -4);
    }

    [$meta['owner'], $meta['repo']] = explode('/', $ownerAndRepo);

    return $meta;
}
