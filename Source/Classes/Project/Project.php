<?php

namespace Saeghe\Saeghe\Classes\Project;

use Saeghe\Datatype\Collection;
use Saeghe\FileManager\Filesystem\Directory;
use Saeghe\FileManager\Filesystem\File;
use Saeghe\FileManager\Filesystem\Filename;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Meta\Meta;
use Saeghe\Saeghe\Git\Repository;

class Project
{
    public File $config_file;
    public File $meta_file;
    public Directory $packages_directory;
    public Config $config;
    public Meta $meta;

    public Collection $packages;

    public function __construct(
        public Directory $root,
    ) {
        $this->config_file = (new Filename('saeghe.config.json'))->file($this->root);
        $this->meta_file = (new Filename('saeghe.config-lock.json'))->file($this->root);
        $this->packages = new Collection();
    }

    public function config(Config $config): static
    {
        $this->config = $config;
        $this->packages_directory = $this->config->packages_directory->directory($this->root);

        return $this;
    }

    public function package_directory(Repository $repository): Directory
    {
        return $this->packages_directory->subdirectory("$repository->owner/$repository->repo");
    }
}
