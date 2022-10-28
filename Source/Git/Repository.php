<?php

namespace Saeghe\Saeghe\Git;

use function Saeghe\Saeghe\Providers\GitHub\clone_to;
use function Saeghe\Saeghe\Providers\GitHub\download;
use function Saeghe\Saeghe\Providers\GitHub\extract_owner;
use function Saeghe\Saeghe\Providers\GitHub\extract_repo;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_commit_hash;
use function Saeghe\Saeghe\Providers\GitHub\find_latest_version;
use function Saeghe\Saeghe\Providers\GitHub\find_version_hash;
use function Saeghe\Saeghe\Providers\GitHub\has_release;

class Repository
{
    public const DEVELOPMENT_VERSION = 'development';
    public string $version;
    public string $hash;

    /**
     * $owner and $repo are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public string $owner,
        public string $repo,
    ) {}

    public static function fromUrl(string $packageUrl): static
    {
        $owner = extract_owner($packageUrl);
        $repo = extract_repo($packageUrl);

        return new static($owner, $repo);
    }

    public static function fromMeta($meta): static
    {
        return (new static($meta['owner'], $meta['repo']))
            ->version($meta['version'])
            ->hash($meta['hash']);
    }

    public function version(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function hash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function latestVersion(): static
    {
        $this->version = has_release($this->owner, $this->repo)
            ? find_latest_version($this->owner, $this->repo)
            : self::DEVELOPMENT_VERSION;

        return $this;
    }

    public function detectHash(): static
    {
        $this->hash = $this->version !== self::DEVELOPMENT_VERSION
            ? find_version_hash($this->owner, $this->repo, $this->version)
            : find_latest_commit_hash($this->owner, $this->repo);

        return $this;
    }

    public function download(string $destination): bool
    {
        if ($this->version === self::DEVELOPMENT_VERSION) {
            return clone_to($destination, $this->owner, $this->repo);
        }

        return download($destination, $this->owner, $this->repo, $this->version);
    }

    public function is(self $repository): bool
    {
        return $repository->owner === $this->owner && $repository->repo === $this->repo;
    }
}
