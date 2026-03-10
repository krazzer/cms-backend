<?php

namespace KikCMS\Entity\File;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FilePathService
{
    public function __construct(
        #[Autowire('%cms.storage.dir%')] public string $storageDir,
    ) {}

    public function getFilePath($file): string
    {
        $targetFilename = $file->getFileName();
        return $this->storageDir . '/' . $targetFilename;
    }
}