<?php

namespace Saeghe\Saeghe\Classes\Config;

use Saeghe\Datatype\Collection;
use Saeghe\Datatype\Map;
use Saeghe\FileManager\Filesystem\Filename;
use Saeghe\Saeghe\Git\Repository;

class Config
{
    public function __construct(
        public Map $map,
        public Collection $excludes,
        public Collection $entry_points,
        public Map $executables,
        public Filename $packages_directory,
        public Map $repositories,
    ) {}

    public static function init(): static
    {
        return new static(
            new Map(),
            new Collection(),
            new Collection(),
            new Map(),
            new Filename('Packages'),
            new Map(),
        );
    }

    public static function from_array(array $config): static
    {
        $config['map'] = $config['map'] ?? [];
        $config['excludes'] = $config['excludes'] ?? [];
        $config['executables'] = $config['executables'] ?? [];
        $config['entry-points'] = $config['entry-points'] ?? [];
        $config['packages'] = $config['packages'] ?? [];

        $map = new Map();
        $excludes = new Collection();
        $executables = new Map();
        $entry_points = new Collection();
        $packages_directory = new Filename($config['packages-directory'] ?? 'Packages');
        $repositories = new Map();

        foreach ($config['map'] as $namespace => $path) {
            $map->push(new NamespaceFilePair($namespace, new Filename($path)));
        }

        foreach ($config['excludes'] as $exclude) {
            $excludes->push(new Filename($exclude));
        }
        
        foreach ($config['executables'] as $symlink => $file) {
            $executables->push(new LinkPair(new Filename($symlink), new Filename($file)));
        }

        foreach ($config['entry-points'] as $entrypoint) {
            $entry_points->push(new Filename($entrypoint));
        }
        
        foreach ($config['packages'] as $package_url => $version) {
            $repositories->push(new Library($package_url, Repository::from_url($package_url)->version($version)));
        }

        return new static($map, $excludes, $entry_points, $executables, $packages_directory, $repositories);
    }

    public function to_array(): array
    {
        $array = [
            'map' => [],
            'entry-points' => [],
            'excludes' => [],
            'executables' => [],
            'packages-directory' => 'Packages',
            'packages' => [],
        ];

        $this->map->each(function (NamespaceFilePair $namespace_file) use (&$array) {
            $array['map'][$namespace_file->namespace()] = $namespace_file->filename()->string();
        });
        $this->entry_points->each(function (Filename $filename) use (&$array) {
            $array['entry-points'][] = $filename->string();
        });
        $this->excludes->each(function (Filename $filename) use (&$array) {
            $array['excludes'][] = $filename->string();
        });
        $this->executables->each(function (LinkPair $link) use (&$array) {
            $array['executables'][$link->symlink()->string()] = $link->source()->string();
        });
        $array['packages-directory'] = $this->packages_directory->string();
        $this->repositories->each(function (Library $library) use (&$array) {
            $array['packages'][$library->key] = $library->repository()->version;
        });
            
        return $array;
    }
}