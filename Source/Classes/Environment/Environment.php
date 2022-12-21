<?php

namespace Saeghe\Saeghe\Classes\Environment;

use Saeghe\FileManager\Filesystem\Directory;
use Saeghe\FileManager\Filesystem\File;
use function Saeghe\FileManager\Resolver\root;

class Environment
{
    public Directory $pwd;
    public File $credential_file;

    public function __construct(
        public Directory $saeghe,
    ) {
        $this->pwd = Directory::from_string(root());
        $this->credential_file = $this->saeghe->file('credentials.json');
    }
}
