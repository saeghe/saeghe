<?php

namespace Saeghe\Saeghe\Config;

use Saeghe\Saeghe\FileManager\Filesystem\Filename;
use Saeghe\Saeghe\Package;

class Config
{
    /**
     * $map, $entryPoints, $excludes, $executables and $packagesDirectory are readonly.
     *  DO NOT modify them!
     */
    public function __construct(
        public Map $map,
        public EntryPoints $entry_points,
        public Excludes $excludes,
        public Executables $executables,
        public Filename $packages_directory,
        public Packages $packages,
    ) {}

    public static function init(): static
    {
        return new static(new Map(), new EntryPoints(), new Excludes(), new Executables(), new Filename('Packages'), new Packages());
    }

    public static function from_array($config): static
    {
        $packages = [];
        foreach ($config['packages'] ?? [] as $package_url => $version) {
            $packages[$package_url] = Package::from_url($package_url)->version($version);
        }

        return new static(
            map: new Map($config['map'] ?? []),
            entry_points: new EntryPoints($config['entry-points'] ?? []),
            excludes: new Excludes($config['excludes'] ?? []),
            executables: new Executables($config['executables'] ?? []),
            packages_directory: new Filename($config['packages-directory'] ?? 'Packages'),
            packages: new Packages($packages),
        );
    }

    public function to_array(): array
    {
        $packages = [];
        foreach ($this->packages as $package_url => $package) {
            $packages[$package_url] = $package->version;
        }

        return [
            'map' => $this->map->items(),
            'entry-points' => $this->entry_points->items(),
            'excludes' => $this->excludes->items(),
            'executables' => $this->executables->items(),
            'packages-directory' => $this->packages_directory->string(),
            'packages' => $packages,
        ];
    }
}
