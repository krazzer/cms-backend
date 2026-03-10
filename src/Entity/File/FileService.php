<?php

namespace KikCMS\Entity\File;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileService
{
    public function __construct(
        #[Autowire('%cms.storage.dir%')] public string $storageDir,
        #[Autowire('%cms.media.public_dir%')] public string $publicMediaDir,
        #[Autowire('%cms.media.public_subdir%')] public string $publicMediaSubDir,
        #[Autowire('%cms.media.url_prefix%')] public string $publicMediaUrlPrefix,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private FileRepository $fileRepository,
        private FileStorageService $fileStorageService,
        private FileHashService $fileHashService,
        private FileThumbnailService $fileThumbnailService,
    ) {}

    public function upload(UploadedFile $uploadedFile, ?File $folder): File
    {
        $folderId = $folder?->getId();

        $file = new File()
            ->setName($uploadedFile->getClientOriginalName())
            ->setExtension($uploadedFile->guessExtension() ?? $uploadedFile->getClientOriginalExtension())
            ->setMimetype($uploadedFile->getMimeType())
            ->setCreated(new DateTimeImmutable)
            ->setUpdated(new DateTimeImmutable)
            ->setIsFolder(false)
            ->setFolder($folderId ? $this->fileRepository->find($folderId) : null)
            ->setSize($uploadedFile->getSize())
            ->setUser($this->security->getUser() ?: null);

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        $this->fileStorageService->storeUploadedFile($uploadedFile, $file);
        $this->fileHashService->updateHash($file);
        $this->fileThumbnailService->generateThumbnail($file);

        return $file;
    }

    public function getFilesInFolder(?int $folderId = null): array
    {
        $criteria = ['isFolder' => false];

        if ($folderId === null) {
            $criteria['folder'] = null;
        } else {
            $criteria['folder'] = $folderId;
        }

        return $this->fileRepository->findBy($criteria, ['name' => 'ASC']);
    }
}