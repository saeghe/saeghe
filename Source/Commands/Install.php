<?php

namespace Saeghe\Saeghe\Commands\Install;

use ZipArchive;

function run()
{
    global $meta;

    $packagesDirectory = find_or_create_packages_directory();

    foreach ($meta['packages'] as $package => $meta) {
        install($package, $meta, $packagesDirectory);
    }
}

function install($package, $meta, $packagesDirectory)
{
    if (package_has_release($package, $meta)) {
        download_package($packagesDirectory, $package, $meta['version'], $meta);
    } else {
        clone_package($packagesDirectory, $package, $meta['version'], $meta);
    }
}

function find_or_create_packages_directory()
{
    global $packagesDirectory;

    if (! file_exists($packagesDirectory)) {
        mkdir($packagesDirectory);
    }

    return $packagesDirectory;
}

function package_has_release($package, $meta)
{
    global $credentials;

    $output = shell_exec('curl -H "Accept: application/vnd.github+json" -H "Authorization: Bearer ' . $credentials['github.com']['token'] . '" ' .  "https://api.github.com/repos/{$meta['owner']}/{$meta['repo']}/releases/latest | grep 'tag_name'");

    return ! is_null($output);
}

function clone_package($packageDirectory, $package, $version, $meta)
{
    $destination = "$packageDirectory{$meta['owner']}/{$meta['repo']}";

    shell_exec("rm -fR $destination");
    shell_exec("git clone $package $destination");

    $meta['hash'] = shell_exec("git --git-dir=$destination/.git rev-parse HEAD");
    $meta['version'] = 'development';

    return $meta;
}

function download_package($packageDirectory, $package, $version, $meta)
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
