<?php

namespace Saeghe\Saeghe\Providers\GitHub;

function isSsh(string $packageUrl): bool
{
    return str_starts_with($packageUrl, 'git@');
}

function extract_owner($packageUrl): string
{
    if (isSsh($packageUrl)) {
        $ownerAndRepo = str_replace('git@github.com:', '', $packageUrl);
    } else {
        $ownerAndRepo = str_replace('https://github.com/', '', $packageUrl);
    }

    if (str_ends_with($ownerAndRepo, '.git')) {
        $ownerAndRepo = substr_replace($ownerAndRepo, '', -4);
    }

    return explode('/', $ownerAndRepo)[0];
}

function extract_repo($packageUrl): string
{
    if (isSsh($packageUrl)) {
        $ownerAndRepo = str_replace('git@github.com:', '', $packageUrl);
    } else {
        $ownerAndRepo = str_replace('https://github.com/', '', $packageUrl);
    }

    if (str_ends_with($ownerAndRepo, '.git')) {
        $ownerAndRepo = substr_replace($ownerAndRepo, '', -4);
    }

    return explode('/', $ownerAndRepo)[1];
}

function github_token(?string $token = null): string
{
    if ($token) {
        putenv('GITHUB_TOKEN=' . $token);
    }

    return getenv('GITHUB_TOKEN', true);
}

function has_release($owner, $repo): bool
{
    $output = shell_exec(
        'curl -s -H "Accept: application/vnd.github+json" -H "Authorization: Bearer ' . github_token() . '" '
        .  "https://api.github.com/repos/$owner/$repo/releases/latest"
    );

    return isset(json_decode($output, true)['tag_name']);
}

function find_latest_version($owner, $repo): string
{
    $output = shell_exec('curl -s -H "Accept: application/vnd.github+json" -H "Authorization: Bearer '
        . github_token()
        . '" '
        .  "https://api.github.com/repos/$owner/$repo/releases/latest");

    return json_decode($output, true)['tag_name'];
}

function find_version_hash($owner, $repo, $version): string
{
    $output = shell_exec('curl -s -H "Accept: application/vnd.github+json" -H "Authorization: Bearer '
        . github_token()
        . '" '
        .  "https://api.github.com/repos/$owner/$repo/git/ref/tags/$version");

    return json_decode($output, true)['object']['sha'];
}

function find_latest_commit_hash($owner, $repo): string
{
    $output = shell_exec('curl -s -H "Accept: application/vnd.github+json" -H "Authorization: Bearer '
        . github_token()
        . '" '
        .  "https://api.github.com/repos/$owner/$repo/commits");

    return json_decode($output, true)[0]['sha'];
}

function download($destination, $owner, $repo, $version): bool
{
    $token = github_token();
    $parentDirectory = dirname($destination);

    if (! file_exists($parentDirectory)) {
        mkdir($parentDirectory, 0755, true);
    }

    $parentDirectory = $parentDirectory . '/';

    $zipFile = $parentDirectory . $repo . '.zip';

    $fp = fopen ($zipFile, 'w+');
    $ch = curl_init("https://github.com/$owner/$repo/zipball/$version");
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    $zip = new \ZipArchive;
    $res = $zip->open($zipFile);

    if ($res === TRUE) {
        $zip->extractTo($parentDirectory);
        $zip->close();
    } else {
        var_dump($res);
    }

    shell_exec('rm -f ' . $zipFile);

    $files = scandir($parentDirectory);

    $directory = array_reduce($files, function ($carry, $filename) use ($owner, $repo) {
        return str_starts_with($filename, "$owner-$repo-") ? $filename : $carry;
    });

    return rename($parentDirectory . $directory, $destination);
}

function clone_to($destination, $owner, $repo): bool
{
    $output = passthru("git clone git@github.com:$owner/$repo.git $destination");

    return  $output === null;
}
