<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\Config\Config;
use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Git\Repository;

class Package extends Repository
{
    public function config_path(Project $project, Config $config): File
    {
        return $this->root($project, $config)->file('saeghe.config.json');
    }

    public function build_root(Project $project, Config $config): Directory
    {
        return $project->build_root->subdirectory("{$config->packages_directory}/{$this->owner}/{$this->repo}");
    }

    public function root(Project $project, Config $config): Directory
    {
        return $project->root->subdirectory("{$config->packages_directory}/{$this->owner}/{$this->repo}");
    }

    public function is_downloaded(Project $project, Config $config): bool
    {
        return $this->root($project, $config)->exists();
    }

    public function config(Project $project, Config $config): Config
    {
        return $this->config_path($project, $config)->exists()
            ? Config::from_array(Json\to_array($this->config_path($project, $config)))
            : Config::init();
    }
}
