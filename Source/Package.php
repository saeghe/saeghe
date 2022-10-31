<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\FileSystem\Address;
use Saeghe\Saeghe\Git\Repository;

class Package extends Repository
{
    public function configPath(Project $project, Config $config): Address
    {
        return $this->root($project, $config)->append('saeghe.config.json');
    }

    public function buildRoot(Project $project, Config $config): Address
    {
        return $project->buildRoot->append("{$config->packagesDirectory}/{$this->owner}/{$this->repo}");
    }

    public function root(Project $project, Config $config): Address
    {
        return $project->root->append("{$config->packagesDirectory}/{$this->owner}/{$this->repo}");
    }
}
