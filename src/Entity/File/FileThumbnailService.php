<?php

namespace KikCMS\Entity\File;

use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
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
    ) {}

    public function generateThumbnail(File $file): void
    {
        if ($file->isImage()) {
            $targetPath = $this->filePathService->getFilePath($file);

            $imagine = new Imagine;
            $image   = $imagine->open($targetPath);

            $fullThumbDir = $this->publicMediaDir . '/' . $this->thumbnailDir . '/' . $this->thumbnailType;
            $this->filesystem->mkdir($fullThumbDir);

            $thumbFileName = $file->getHash() . '.' . $this->thumbnailExt;
            $thumbPath     = $fullThumbDir . '/' . $thumbFileName;

            $thumbnail = $image->thumbnail(new Box($this->thumbnailWidth, $this->thumbnailHeight));
            $thumbnail->save($thumbPath);
        }
    }


    public function getThumb(File $file): ?string
    {
        if (!$file->isImage()) {
            return null;
        }

        $thumbFileName = $file->getHash() . '.' . $this->thumbnailExt;
        return '/' . $this->publicMediaUrlPrefix . '/' . $this->thumbnailDir . '/' . $this->thumbnailType . '/' . $thumbFileName;
    }

    public function deleteThumbnails(File $file): void
    {
        $fullThumbDir = $this->publicMediaDir . '/' . $this->thumbnailDir . '/' . $this->thumbnailType;
        $thumbFileName = $file->getHash() . '.' . $this->thumbnailExt;
        $thumbPath = $fullThumbDir . '/' . $thumbFileName;

        if ($this->filesystem->exists($thumbPath)) {
            $this->filesystem->remove($thumbPath);
        }
    }
}