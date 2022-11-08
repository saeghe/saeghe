<?php

namespace Saeghe\Saeghe;

use Saeghe\Exception\CredentialCanNotBeSetException;
use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\DirectoryAddress;
use Saeghe\Saeghe\FileManager\FileAddress;
use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

class Project
{
    /**
     * $buildRoot is readonly.
     *  DO NOT modify it!
     */
    public Address $build_root;

    /**
     * $root, $environment, $config, $config_lock, $credentials are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public DirectoryAddress $root,
        public string  $environment,
        public FileAddress $config,
        public FileAddress $config_lock,
        public FileAddress $credentials,
    ) {
        $this->build_root = $this->root->append('builds/' . $this->environment);
    }

    public function set_env_credentials(): void
    {
        $environmentToken = github_token();

        if (strlen($environmentToken) > 0) {
            return;
        }

        if (! $this->credentials->exists()) {
            throw new CredentialCanNotBeSetException('There is no credential file. Please use the `credential` command to add your token.');
        }

        $credential = Json\to_array($this->credentials->to_string());
        github_token($credential[GITHUB_DOMAIN]['token'] ?? '');
    }
}
