<?php

namespace KikCMS\Domain\App\Twig;

use KikCMS\Entity\File\FilePublicService;
use Twig\Attribute\AsTwigFunction;

readonly class AppExtension
{
    public function __construct(
        private FilePublicService $filePublicService
    ) {}

    #[AsTwigFunction('media_file')]
    public function mediaFile(int $fileId): ?string
    {
        return $this->filePublicService->getUrlCreateIfMissingById($fileId);
    }

    #[AsTwigFunction('media_file_bg')]
    public function mediaFileBg(int $fileId): ?string
    {
        return 'background-image: url(' . $this->mediaFile($fileId) . ');';
    }
}