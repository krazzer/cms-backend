<?php

namespace KikCMS\Entity\File;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

class FilePublicService
{
    public function __construct(
        #[Autowire('%cms.storage.dir%')] public string $storageDir,
        #[Autowire('%cms.media.public_dir%')] public string $publicMediaDir,
        #[Autowire('%cms.media.public_subdir%')] public string $publicMediaSubDir,
        #[Autowire('%cms.media.url_prefix%')] public string $publicMediaUrlPrefix,
        private readonly Filesystem $filesystem,
        private readonly SluggerInterface $slugger,
    ) {}

    public function getUrlCreateIfMissing(File $file, bool $private = false): string
    {
        $fileName = $private ? $file->getFileName(true) : $this->getPublicFileName($file);

        $publicFilePath = $this->publicMediaDir . '/' . $this->publicMediaSubDir . '/' . $fileName;

        $this->filesystem->mkdir(dirname($publicFilePath));

        if ( ! file_exists($publicFilePath)) {
            $targetPath = $this->storageDir . '/' . $file->getFileName($private);

            $this->filesystem->symlink($targetPath, $publicFilePath);
        }

        $url = '/' . $this->publicMediaUrlPrefix . '/' . $fileName;

        if ($secondsUpdated = $file->secondsUpdated()) {
            $url .= '?u=' . $secondsUpdated;
        }

        return $url;
    }

    private function getPublicFileName(File $file): string
    {
        $slugName = $this->slugger->slug(pathinfo($file->getName(), PATHINFO_FILENAME))->toString();
        return $file->getId() . '-' . $slugName . '.' . $file->getExtension();
    }
}