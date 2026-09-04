<?php

namespace KikCMS\Entity\File;

use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use KikCMS\Kernel;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

readonly class FileThumbnailService
{
    public function __construct(
        #[Autowire('%cms.media.public_dir%')] public string $publicMediaDir,
        #[Autowire('%cms.thumbnail.type%')] private string $thumbnailType,
        #[Autowire('%cms.media.url_prefix%')] public string $publicMediaUrlPrefix,
        #[Autowire('%cms.thumbnail.directory%')] private string $thumbnailDir,
        #[Autowire('%cms.thumbnail.extension%')] private string $thumbnailExt,
        #[Autowire('%cms.thumbnail.width%')] private string $thumbnailWidth,
        #[Autowire('%cms.thumbnail.height%')] private string $thumbnailHeight,
        private FilePathService $filePathService,
        private Filesystem $filesystem,
        private Kernel $kernel,
        private Packages $assetPackages,
    ) {}

    public function deleteThumbnails(File $file): void
    {
        $fullThumbDir  = $this->publicMediaDir . '/' . $this->thumbnailDir . '/' . $this->thumbnailType;
        $thumbFileName = $file->getHash() . '.' . $this->thumbnailExt;
        $thumbPath     = $fullThumbDir . '/' . $thumbFileName;

        if ($this->filesystem->exists($thumbPath)) {
            $this->filesystem->remove($thumbPath);
        }
    }

    public function generate(File $file): void
    {
        if ($file->isImage()) {
            $targetPath = $this->filePathService->getFilePath($file);

            $thumbPath = $this->getPath($file);

            $this->filesystem->mkdir(dirname($thumbPath));

            $image     = (new Imagine)->open($targetPath);
            $thumbnail = $image->thumbnail(new Box($this->thumbnailWidth, $this->thumbnailHeight));
            $thumbnail->save($thumbPath);
        }
    }

    public function getUrl(File $file): ?string
    {
        if ( ! $file->isImage()) {
            return null;
        }

        $thumbFileName = $this->getFileName($file);

        return $this->assetPackages->getUrl(Kernel::SUBDIR_MEDIA_THUMBS_DEFAULT . '/' . $thumbFileName);
    }

    public function getPath(File $file): ?string
    {
        return $this->kernel->getPublicDir(Kernel::SUBDIR_MEDIA_THUMBS_DEFAULT . '/' . $this->getFileName($file));
    }

    public function getFileName(File $file): ?string
    {
        return $file->getHash() . '.' . $this->thumbnailExt;
    }

    public function getOrGenerateUrl(File $file): ?string
    {
        if ( ! $file->isImage()) {
            return null;
        }

        if ( ! $this->filesystem->exists($this->getPath($file))) {
            $this->generate($file);
        }

        return $this->getUrl($file);
    }
}