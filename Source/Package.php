<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\Git\Repository;
use Saeghe\Saeghe\FileManager\Directory;

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
}
