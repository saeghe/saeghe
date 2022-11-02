<?php

namespace Saeghe\Saeghe\Providers\GitHub;

use function Saeghe\Saeghe\FileManager\File\delete;

const GITHUB_DOMAIN = 'github.com';
const GITHUB_URL = 'https://github.com/';
const GITHUB_API_URL = 'https://api.github.com/';
const GITHUB_SSH_URL = 'git@github.com:';

function is_ssh(string $package_url): bool
{
    return str_starts_with($package_url, 'git@');
}

function extract_owner($package_url): string
{
    if (is_ssh($package_url)) {
        $owner_and_repo = str_replace(GITHUB_SSH_URL, '', $package_url);
    } else {
        $owner_and_repo = str_replace(GITHUB_URL, '', $package_url);
    }

    if (str_ends_with($owner_and_repo, '.git')) {
        $owner_and_repo = substr_replace($owner_and_repo, '', -4);
    }

    return explode('/', $owner_and_repo)[0];
}

function extract_repo($package_url): string
{
    if (is_ssh($package_url)) {
        $owner_and_repo = str_replace(GITHUB_SSH_URL, '', $package_url);
    } else {
        $owner_and_repo = str_replace(GITHUB_URL, '', $package_url);
    }

    if (str_ends_with($owner_and_repo, '.git')) {
        $owner_and_repo = substr_replace($owner_and_repo, '', -4);
    }

    return explode('/', $owner_and_repo)[1];
}

function github_token(?string $token = null): string
{
    if ($token) {
        putenv('GITHUB_TOKEN=' . $token);
    }

    return getenv('GITHUB_TOKEN', true);
}

function get_json(string $api_sub_url): array
{
    $token = github_token();

    $ch = curl_init(GITHUB_API_URL . $api_sub_url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Saeghe');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/vnd.github+raw",
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
    $parent_directory = dirname($destination);

    if (! \file_exists($parent_directory)) {
        mkdir($parent_directory, 0755, true);
    }

    $parent_directory = $parent_directory . '/';

    $zip_file = $parent_directory . $repo . '.zip';

    $fp = fopen ($zip_file, 'w+');
    $ch = curl_init(GITHUB_URL . "$owner/$repo/zipball/$version");
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    $zip = new \ZipArchive;
    $res = $zip->open($zip_file);

    if ($res === TRUE) {
        $zip->extractTo($parent_directory);
        $zip->close();
    } else {
        var_dump($res);
    }

    delete($zip_file);

    $files = scandir($parent_directory);

    $directory = array_reduce($files, function ($carry, $filename) use ($owner, $repo) {
        return str_starts_with($filename, "$owner-$repo-") ? $filename : $carry;
    });

    return rename($parent_directory . $directory, $destination);
}

function clone_to($destination, $owner, $repo): bool
{
    $github_ssh_url = GITHUB_SSH_URL;
    $output = passthru("git clone $github_ssh_url$owner/$repo.git $destination");

    return  $output === null;
}

function file_exists(string $owner, string $repo, string $hash, string $path): bool
{
    return false !== @file_get_contents("https://raw.githubusercontent.com/$owner/$repo/$hash/$path");
}
