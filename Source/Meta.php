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

    public static function from_array(array $meta): static
    {
        $packages = [];
        foreach ($meta['packages'] ?? [] as $package_url => $meta) {
            $packages[$package_url] = Package::from_meta($meta);
        }

        return new static($packages);
    }

    public function to_array(): array
    {
        $packages = [];
        foreach ($this->packages as $package_url => $package) {
            $packages[$package_url] = [
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
