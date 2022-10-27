<?php

namespace Saeghe\Saeghe;

class Meta
{
    public function __construct(
        public array $packages,
    ) {}

    public static function init(): static
    {
        return new static([]);
    }

    public static function fromArray(array $meta): static
    {
        $packages = [];
        foreach ($meta['packages'] ?? [] as $packageUrl => $meta) {
            $packages[$packageUrl] = Package::fromMeta($meta);
        }

        return new static($packages);
    }

    public function toArray(): array
    {
        $packages = [];
        foreach ($this->packages as $packageUrl => $package) {
            $packages[$packageUrl] = [
                'owner' => $package->owner,
                'repo' => $package->repo,
                'version' => $package->version,
                'hash' => $package->hash,
            ];
        }

        return [
            'packages' => $packages,
        ];
    }
}
