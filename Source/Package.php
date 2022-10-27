<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\Git\Repository;

class Package extends Repository
{
    public function configPath(Project $project, Config $config): string
    {
        return $this->root($project, $config) . 'saeghe.config.json';
    }

    public function buildRoot(Project $project, Config $config): string
    {
        return $project->buildRoot . $config->packagesDirectory . '/' . $this->owner . '/' . $this->repo . '/';
    }

    public function root($project, $config)
    {
        return $project->root . $config->packagesDirectory . '/' . $this->owner . '/' . $this->repo . '/';
    }
}
