<?php

namespace KikCMS\Entity\File;

use Doctrine\ORM\EntityManagerInterface;

readonly class FileHashService
{
    public function __construct(
        private FilePathService $filePathService,
        protected EntityManagerInterface $entityManager,
    ) {}

    public function updateHash(File $file): void
    {
        $targetPath = $this->filePathService->getFilePath($file);

        $file->setHash(md5_file($targetPath));
        $this->entityManager->flush();
    }
}