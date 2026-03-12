<?php

namespace KikCMS\Entity\File;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileStorageService
{
    public function __construct(
        #[Autowire('%cms.media.public_dir%')] public string $publicMediaDir,
        private Filesystem $filesystem,
        private FilePathService $filePathService,
    ) {}

    public function storeUploadedFile(UploadedFile $uploadedFile, File $file): void
    {
        $targetFilename = $file->getFileName();
        $targetPath     = $this->filePathService->getFilePath($file);

        $this->filesystem->mkdir(dirname($targetPath));
        $uploadedFile->move(dirname($targetPath), $targetFilename);
    }

    public function deleteFile(File $file): void
    {
        $filePath = $this->filePathService->getFilePath($file);
        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }
}