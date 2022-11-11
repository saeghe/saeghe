<?php

namespace Saeghe\Saeghe\Config;

use Saeghe\Saeghe\Package;

class Meta
{
    public function __construct(
        public Packages $packages,
    ) {}

    public static function init(): static
    {
        return new static(new Packages());
    }

    public static function from_array(array $meta): static
    {
        $packages = [];
        foreach ($meta['packages'] ?? [] as $package_url => $meta) {
            $packages[$package_url] = Package::from_meta($meta);
        }

        return new static(new Packages($packages));
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
