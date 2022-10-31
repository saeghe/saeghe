<?php

namespace Saeghe\Saeghe\Providers\GitHub;

use function Saeghe\FileManager\File\delete;

const GITHUB_DOMAIN = 'github.com';
const GITHUB_URL = 'https://github.com/';
const GITHUB_API_URL = 'https://api.github.com/';
const GITHUB_SSH_URL = 'git@github.com:';

function isSsh(string $packageUrl): bool
{
    return str_starts_with($packageUrl, 'git@');
}

function extract_owner($packageUrl): string
{
    if (isSsh($packageUrl)) {
        $ownerAndRepo = str_replace(GITHUB_SSH_URL, '', $packageUrl);
    } else {
        $ownerAndRepo = str_replace(GITHUB_URL, '', $packageUrl);
    }

    if (str_ends_with($ownerAndRepo, '.git')) {
        $ownerAndRepo = substr_replace($ownerAndRepo, '', -4);
    }

    return explode('/', $ownerAndRepo)[0];
}

function extract_repo($packageUrl): string
{
    if (isSsh($packageUrl)) {
        $ownerAndRepo = str_replace(GITHUB_SSH_URL, '', $packageUrl);
    } else {
        $ownerAndRepo = str_replace(GITHUB_URL, '', $packageUrl);
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

function get_json(string $apiSubUrl): array
{
    $token = github_token();

    $ch = curl_init(GITHUB_API_URL . $apiSubUrl);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Saeghe');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/vnd.github+json",
        "Authorization: Bearer $token",
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);

    return json_decode($output, true);
}

function has_release($owner, $repo): bool
{
    $json = get_json("repos/$owner/$repo/releases/latest");

    return isset($json['tag_name']);
}

function find_latest_version($owner, $repo): string
{
    $json = get_json("repos/$owner/$repo/releases/latest");

    return $json['tag_name'];
}

function find_version_hash($owner, $repo, $version): string
{
    $json = get_json("repos/$owner/$repo/git/ref/tags/$version");

    return $json['object']['sha'];
}

function find_latest_commit_hash($owner, $repo): string
{
    $json = get_json("repos/$owner/$repo/commits");

    return $json[0]['sha'];
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
    $ch = curl_init(GITHUB_URL . "$owner/$repo/zipball/$version");
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

    delete($zipFile);

    $files = scandir($parentDirectory);

    $directory = array_reduce($files, function ($carry, $filename) use ($owner, $repo) {
        return str_starts_with($filename, "$owner-$repo-") ? $filename : $carry;
    });

    return rename($parentDirectory . $directory, $destination);
}

function clone_to($destination, $owner, $repo): bool
{
    $githubSshUrl = GITHUB_SSH_URL;
    $output = passthru("git clone $githubSshUrl$owner/$repo.git $destination");

    return  $output === null;
}
