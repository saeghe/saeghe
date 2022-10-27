<?php

namespace Saeghe\Saeghe;

class Project
{
    /**
     * $buildRoot is readonly.
     *  DO NOT modify it!
     */
    public string $buildRoot;

    /**
     * $root, $environment, $configFilePath, $configLockFilePath, $credentialsPath are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public string $root,
        public string $environment,
        public string $configFilePath,
        public string $configLockFilePath,
        public string $credentialsPath,
    ) {
        $this->buildRoot = $this->root . 'builds/' . $this->environment . '/';
    }
}
