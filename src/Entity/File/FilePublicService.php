<?php

namespace KikCMS\Entity\File;

use KikCMS\Domain\App\Path\PathConfig;
use KikCMS\Kernel;
use Symfony\Component\Asset\Packages;
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
        private readonly Kernel $kernel,
        private readonly Filesystem $filesystem,
        private readonly SluggerInterface $slugger,
        private readonly Packages $assetPackages,
    ) {}

    public function getUrlCreateIfMissing(File $file, bool $private = false): string
    {
        $fileName = $private ? $file->getFileName(true) : $this->getPublicFileName($file);

        $publicFilePath = $this->kernel->getPublicDir(PathConfig::SUBDIR_MEDIA_FILES . '/' . $fileName);

        $this->filesystem->mkdir(dirname($publicFilePath));

        if ( ! file_exists($publicFilePath)) {
            $privateFileName = $file->getFileName($private);
            $targetPath      = $this->kernel->getDir(PathConfig::DIR_STORAGE . '/' . $privateFileName);

            $this->filesystem->symlink($targetPath, $publicFilePath);
        }

        $url = $this->assetPackages->getUrl(PathConfig::SUBDIR_MEDIA_FILES . '/' . $fileName);

        if ($secondsUpdated = $file->secondsUpdated()) {
            $url .= '?u=' . $secondsUpdated;
        }

        return $url;
    }

    public function deletePublicFiles(File $file): void
    {
        $publicDir = $this->publicMediaDir . '/' . $this->publicMediaSubDir;
        $pattern   = $publicDir . '/' . $file->getId() . '-*';

        foreach (glob($pattern) as $publicFile) {
            if (is_link($publicFile) || is_file($publicFile)) {
                $this->filesystem->remove($publicFile);
            }
        }
    }

    private function getPublicFileName(File $file): string
    {
        $slugName = $this->slugger->slug(pathinfo($file->getName(), PATHINFO_FILENAME))->toString();
        return $file->getId() . '-' . $slugName . '.' . $file->getExtension();
    }
}