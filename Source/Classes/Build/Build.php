<?php

namespace Saeghe\Saeghe\Classes\Build;

use Saeghe\Datatype\Map;
use Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\Classes\Config\NamespaceFilePair;
use Saeghe\Saeghe\Classes\Config\NamespacePathPair;
use Saeghe\Saeghe\Classes\Package\Package;
use Saeghe\Saeghe\Classes\Project\Project;

class Build
{
    public Map $import_map;

    public Map $namespace_map;

    public function __construct(
        public Project $project,
        public string $environment,
    ) {
        $this->import_map = new Map();
    }

    public function root(): Directory
    {
        return $this->project->root->subdirectory('builds')->subdirectory($this->environment);
    }

    public function packages_directory(): Directory
    {
        return $this->root()->subdirectory($this->project->config->packages_directory);
    }

    public function package_root(Package $package): Directory
    {
        return $this->packages_directory()->subdirectory("{$package->repository->owner}/{$package->repository->repo}");
    }

    public function load_namespace_map(): static
    {
        $this->namespace_map = new Map();

        $this->project->packages->each(function (Package $package) {
            $package->config->map->each(function (NamespaceFilePair $namespace_file) use ($package) {
                $this->namespace_map->push(new NamespacePathPair($namespace_file->namespace(), $this->package_root($package)->append($namespace_file->filename())));
            });
        });

        $this->project->config->map->each(function (NamespaceFilePair $namespace_file) {
            $this->namespace_map->push(new NamespacePathPair($namespace_file->namespace(), $this->root()->append($namespace_file->filename())));
        });

        return $this;
    }
}
