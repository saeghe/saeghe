<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\FileSystem\Address;

class Project
{
    /**
     * $buildRoot is readonly.
     *  DO NOT modify it!
     */
    public Address $buildRoot;

    /**
     * $root, $environment, $configFilePath, $configLockFilePath, $credentialsPath are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public Address $root,
        public string $environment,
        public Address $configFilePath,
        public Address $configLockFilePath,
        public Address $credentialsPath,
    ) {
        $this->buildRoot = $this->root->append('builds/' . $this->environment);
    }
}
