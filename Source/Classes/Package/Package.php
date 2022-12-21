<?php

namespace Saeghe\Saeghe\Classes\Package;

use Saeghe\FileManager\Filesystem\Directory;
use Saeghe\FileManager\Filesystem\File;
use Saeghe\FileManager\Filesystem\Filename;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Git\Repository;

class Package
{
    public File $config_file;
    public Config $config;

    public function __construct(
        public Directory $root,
        public Repository $repository,
    ) {
        $this->config_file = (new Filename('saeghe.config.json'))->file($this->root);
    }

    public function is_downloaded(): bool
    {
        return $this->root->exists();
    }

    public function download(): bool
    {
        return $this->repository->download($this->root);
    }
}
