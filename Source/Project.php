<?php

namespace Saeghe\Saeghe;

class Project
{
    /**
     * $buildRoot is readonly.
     *  DO NOT modify it!
     */
    public Path $buildRoot;

    /**
     * $root, $environment, $configFilePath, $configLockFilePath, $credentialsPath are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public Path $root,
        public string $environment,
        public Path $configFilePath,
        public Path $configLockFilePath,
        public Path $credentialsPath,
    ) {
        $this->buildRoot = $this->root->append('builds/' . $this->environment);
    }
}
