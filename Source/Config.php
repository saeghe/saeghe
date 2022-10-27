<?php

namespace Saeghe\Saeghe;

class Config
{
    public function __construct(
        public readonly array $map,
        public readonly array $entryPoints,
        public readonly array $excludes,
        public readonly array $executables,
        public readonly string $packagesDirectory,
        public array $packages,
    ) {}

    public static function init(): static
    {
        return new static([], [], [], [], 'Packages', []);
    }

    public static function fromArray($config): static
    {
        $packages = [];
        foreach ($config['packages'] ?? [] as $packageUrl => $version) {
            $packages[$packageUrl] = Package::fromUrl($packageUrl)->version($version);
        }

        return new static(
            map: $config['map'] ?? [],
            entryPoints: $config['entry-points'] ?? [],
            excludes: $config['excludes'] ?? [],
            executables: $config['executables'] ?? [],
            packagesDirectory: $config['packages-directory'] ?? 'Packages',
            packages: $packages,
        );
    }

    public function toArray(): array
    {
        $packages = [];
        foreach ($this->packages as $packageUrl => $package) {
            $packages[$packageUrl] = $package->version;
        }

        return [
            'map' => $this->map,
            'entry-points' => $this->entryPoints,
            'excludes' => $this->excludes,
            'executables' => $this->executables,
            'packages-directory' => $this->packagesDirectory,
            'packages' => $packages,
        ];
    }
}
