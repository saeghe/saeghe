<?php

namespace Saeghe\Saeghe;

class Project
{
    public readonly string $buildRoot;

    public function __construct(
        public readonly string $root,
        public readonly string $environment,
        public readonly string $configFilePath,
        public readonly string $configLockFilePath,
        public readonly string $credentialsPath,

    ) {
        $this->buildRoot = $this->root . 'builds/' . $this->environment . '/';
    }
}
