<?php

namespace KikCMS\Entity\File;

use KikCMS\Kernel;

readonly class FilePathService
{
    public function __construct(
        private Kernel $kernel,
    ) {}

    public function getFilePath($file): string
    {
        return $this->kernel->getDir(Kernel::DIR_STORAGE . DIRECTORY_SEPARATOR . $file->getFileName());
    }
}