<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\FileManager\Address;

class Project
{
    /**
     * $buildRoot is readonly.
     *  DO NOT modify it!
     */
    public Address $build_root;

    /**
     * $root, $environment, $configFilePath, $configLockFilePath, $credentialsPath are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public Address $root,
        public string  $environment,
        public Address $config_file_path,
        public Address $config_lock_file_path,
        public Address $credentials_path,
    ) {
        $this->build_root = $this->root->append('builds/' . $this->environment);
    }
}
