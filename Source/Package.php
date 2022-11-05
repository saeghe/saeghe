<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Git\Repository;

class Package extends Repository
{
    public function config_path(Project $project, Config $config): Address
    {
        return $this->root($project, $config)->append('saeghe.config.json');
    }

    public function build_root(Project $project, Config $config): Address
    {
        return $project->build_root->append("{$config->packages_directory}/{$this->owner}/{$this->repo}");
    }

    public function root(Project $project, Config $config): Address
    {
        return $project->root->append("{$config->packages_directory}/{$this->owner}/{$this->repo}");
    }

    public function is_downloaded(Project $project, Config $config): bool
    {
        return Directory\exists($this->root($project, $config)->to_string());
    }

    public function config(Project $project, Config $config): Config
    {
        return $this->config_path($project, $config)->exists()
            ? Config::from_array(Json\to_array($this->config_path($project, $config)->to_string()))
            : Config::init();
    }
}
