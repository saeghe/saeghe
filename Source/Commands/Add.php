<?php

namespace Saeghe\Saeghe\Commands\Add;

use ZipArchive;
use function Saeghe\Cli\IO\Read\argument;

function run()
{
    $package = argument('package');
    $version = argument('version');

    $packagesDirectory = findOrCreatePackagesDirectory();

    add($packagesDirectory, $package, $version);
}

function add($packagesDirectory, $package, $version, $submodule = false)
{
    global $projectRoot;

    $meta = getMetaFromPackage($package);

    if (packageHasRelease($meta)) {
        $meta = downloadPackage($packagesDirectory, $version, $meta);
    } else {
        $meta = clonePackage($packagesDirectory, $version, $meta);
    }

    if (! $submodule) {
        findOrCreateBuildJsonFile($projectRoot, $package, $meta);
    }

    findOrCreateBuildLockFile($projectRoot, $package, $meta);

    $packagePath = $packagesDirectory . '/' . $meta['owner'] . '/' . $meta['repo'] . '/';
    $packageConfig = $packagePath . 'build.json';
    if (file_exists($packageConfig)) {
        $packageSetting = json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR);
        foreach ($packageSetting['packages'] as $subPackage => $version) {
            add($packagesDirectory, $subPackage, $version, true);
        }
    }
}

function findOrCreateBuildJsonFile($projectDirectory, $package, $meta)
{
    $configFile = $projectDirectory . 'build.json';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $config = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $config['packages'][$package] = $meta['version'];

    file_put_contents($projectDirectory . 'build.json', json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
}

function findOrCreateBuildLockFile($projectDirectory, $package, $meta)
{
    $configFile = $projectDirectory . 'build-lock.json';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $lockContent = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $lockContent['packages'][$package] = [
        'version' => $meta['version'],
        'hash' => trim($meta['hash']),
        'owner' => trim($meta['owner']),
        'repo' => trim($meta['repo']),
    ];
    file_put_contents($projectDirectory . 'build-lock.json', json_encode($lockContent, JSON_PRETTY_PRINT) . PHP_EOL);
}

function findOrCreatePackagesDirectory()
{
    global $packagesDirectory;

    if (! file_exists($packagesDirectory)) {
        mkdir($packagesDirectory);
    }

    return $packagesDirectory;
}

function clonePackage($packageDirectory, $version, $meta)
{
    $destination = "$packageDirectory{$meta['owner']}/{$meta['repo']}";

    shell_exec("rm -fR $destination");
    shell_exec("git clone git@github.com:{$meta['owner']}/{$meta['repo']}.git $destination");

    $meta['hash'] = shell_exec("git --git-dir=$destination/.git rev-parse HEAD");
    $meta['version'] = 'development';

    return $meta;
}

function packageHasRelease($meta)
{
    global $credentials;

    $output = shell_exec('curl -H "Accept: application/vnd.github+json" -H "Authorization: Bearer ' . $credentials['github.com']['token'] . '" ' .  "https://api.github.com/repos/{$meta['owner']}/{$meta['repo']}/releases/latest | grep 'tag_name'");

    return ! is_null($output);
}

function downloadPackage($packageDirectory, $version, $meta)
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

function getMetaFromPackage($package)
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
