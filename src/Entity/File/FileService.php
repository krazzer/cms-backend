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
        private FilePublicService $filePublicService,
    ) {}

    public function uploadFiles(array $files, ?string $folderIdParam): array
    {
        $folderId = ($folderIdParam === 'null' || $folderIdParam === '') ? null : (int) $folderIdParam;
        $folder = $folderId ? $this->fileRepository->find($folderId) : null;

        $newFiles = [];
        foreach ($files as $uploadedFile) {
            $newFiles[] = $this->uploadFile($uploadedFile, $folder);
        }

        $allFiles = $this->getFilesInFolder($folderId);

        $formatFile = function (File $file): array {
            return [
                'id'   => $file->getId(),
                'name' => $file->getName(),
                'url'  => $file->isFolder() ? null : $this->filePublicService->getUrlCreateIfMissing($file),
            ];
        };

        return [
            'files'    => array_map($formatFile, $allFiles),
            'newFiles' => array_map($formatFile, $newFiles),
        ];
    }

    private function uploadFile(UploadedFile $uploadedFile, ?File $folder): File
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

    public function createFolder(string $name, ?string $folderIdParam): array
    {
        $folderId = ($folderIdParam === null || $folderIdParam === 'null' || $folderIdParam === '') ? null : (int) $folderIdParam;
        $parent = $folderId ? $this->fileRepository->find($folderId) : null;

        $folder = new File()
            ->setName($name)
            ->setCreated(new DateTimeImmutable())
            ->setUpdated(new DateTimeImmutable())
            ->setIsFolder(true)
            ->setFolder($parent)
            ->setSize(0);

        $this->entityManager->persist($folder);
        $this->entityManager->flush();

        $allFiles = $this->getFilesInFolder($folderId);

        $path = $this->buildPath($folderId);

        $formatFile = function (File $file): array {
            return [
                'id'   => $file->getId(),
                'name' => $file->getName(),
                'url'  => $file->isFolder() ? null : $this->filePublicService->getUrlCreateIfMissing($file),
            ];
        };

        return [
            'files' => array_map($formatFile, $allFiles),
            'path'  => $path,
        ];
    }

    private function buildPath(?int $folderId): array
    {
        $path = [];
        $currentId = $folderId;

        while ($currentId !== null) {
            $folder = $this->fileRepository->find($currentId);
            if (!$folder) {
                break;
            }
            array_unshift($path, [
                'id'   => $folder->getId(),
                'name' => $folder->getName(),
            ]);
            $currentId = $folder->getParent() ? $folder->getParent()->getId() : null;
        }

        return $path;
    }

    //

    private function getFilesInFolder(?int $folderId = null): array
    {
        $criteria = ['folder' => $folderId];

        return $this->fileRepository->findBy($criteria, ['isFolder' => 'DESC', 'name' => 'ASC']);
    }
}