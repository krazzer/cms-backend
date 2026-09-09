<?php

namespace KikCMS\Entity\File;

use KikCMS\Domain\App\Path\PathConfig;
use KikCMS\Kernel;

readonly class FilePathService
{
    public function __construct(
        private Kernel $kernel,
    ) {}

    public function getFilePath($file): string
    {
        return $this->kernel->getDir(PathConfig::DIR_STORAGE . DIRECTORY_SEPARATOR . $file->getFileName());
    }
}