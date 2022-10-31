<?php

namespace Saeghe\Saeghe;

class Config
{
    /**
     * $map, $entryPoints, $excludes, $executables and $packagesDirectory are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public array  $map,
        public array  $entry_points,
        public array  $excludes,
        public array  $executables,
        public string $packages_directory,
        public array  $packages,
    ) {}

    public static function init(): static
    {
        return new static([], [], [], [], 'Packages', []);
    }

    public static function from_array($config): static
    {
        $packages = [];
        foreach ($config['packages'] ?? [] as $package_url => $version) {
            $packages[$package_url] = Package::from_url($package_url)->version($version);
        }

        return new static(
            map: $config['map'] ?? [],
            entry_points: $config['entry-points'] ?? [],
            excludes: $config['excludes'] ?? [],
            executables: $config['executables'] ?? [],
            packages_directory: $config['packages-directory'] ?? 'Packages',
            packages: $packages,
        );
    }

    public function to_array(): array
    {
        $packages = [];
        foreach ($this->packages as $package_url => $package) {
            $packages[$package_url] = $package->version;
        }

        return [
            'map' => $this->map,
            'entry-points' => $this->entry_points,
            'excludes' => $this->excludes,
            'executables' => $this->executables,
            'packages-directory' => $this->packages_directory,
            'packages' => $packages,
        ];
    }
}
