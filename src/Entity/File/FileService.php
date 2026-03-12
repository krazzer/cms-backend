<?php

namespace KikCMS\Entity\File;

use Exception;
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
                'thumb' => $file->isFolder() ? null : $this->fileThumbnailService->getThumb($file),
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
                'thumb' => $file->isFolder() ? null : $this->fileThumbnailService->getThumb($file),
                'url'  => $file->isFolder() ? null : $this->filePublicService->getUrlCreateIfMissing($file),
            ];
        };

        return [
            'files' => array_map($formatFile, $allFiles),
            'path'  => $path,
        ];
    }

    public function openFolder(?string $folderIdParam) : array
    {
        $folderId = ($folderIdParam === null || $folderIdParam === 'null' || $folderIdParam === '') ? null : (int) $folderIdParam;

        $allFiles = $this->getFilesInFolder($folderId);
        $path = $this->buildPath($folderId);

        $formatFile = function (File $file): array {
            return [
                'id'    => $file->getId(),
                'name'  => $file->getName(),
                'thumb' => $file->isFolder() ? null : $this->fileThumbnailService->getThumb($file),
                'url'   => $file->isFolder() ? null : $this->filePublicService->getUrlCreateIfMissing($file),
                'isDir' => $file->isFolder(),
                'key'   => $file->getKey(),
            ];
        };

        return [
            'files' => array_map($formatFile, $allFiles),
            'path'  => $path,
        ];
    }

    public function changeFilename(string $newFileName, int $fileId): array
    {
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $this->filePublicService->deletePublicFiles($file);
        }

        $originalExtension = $file->getExtension();
        $finalFileName = $originalExtension ? $newFileName . '.' . $originalExtension : $newFileName;

        $file->setName($finalFileName);
        $file->setUpdated(new DateTimeImmutable());

        $this->entityManager->flush();

        $folderId = $file->getFolder() ? $file->getFolder()->getId() : null;

        $allFiles = $this->getFilesInFolder($folderId);
        $path = $this->buildPath($folderId);

        $formatFile = function (File $file): array {
            return [
                'id'    => $file->getId(),
                'name'  => $file->getName(),
                'thumb' => $file->isFolder() ? null : $this->fileThumbnailService->getThumb($file),
                'url'   => $file->isFolder() ? null : $this->filePublicService->getUrlCreateIfMissing($file),
                'isDir' => $file->isFolder(),
                'key'   => $file->getKey(),
            ];
        };

        return [
            'files' => array_map($formatFile, $allFiles),
            'path'  => $path,
        ];
    }

    public function changeKey(?string $key, int $fileId): array
    {
        $file = $this->fileRepository->find($fileId);

        $file->setKey($key === '' ? null : $key);
        $file->setUpdated(new DateTimeImmutable());

        $this->entityManager->flush();

        $folderId = $file->getFolder() ? $file->getFolder()->getId() : null;

        $allFiles = $this->getFilesInFolder($folderId);
        $path = $this->buildPath($folderId);

        $formatFile = function (File $file): array {
            return [
                'id'    => $file->getId(),
                'name'  => $file->getName(),
                'thumb' => $file->isFolder() ? null : $this->fileThumbnailService->getThumb($file),
                'url'   => $file->isFolder() ? null : $this->filePublicService->getUrlCreateIfMissing($file),
                'isDir' => $file->isFolder(),
                'key'   => $file->getKey(),
            ];
        };

        return [
            'files' => array_map($formatFile, $allFiles),
            'path'  => $path,
        ];
    }

    public function deleteFiles(array $ids, ?string $folderIdParam): array
    {
        $folderId = ($folderIdParam === null || $folderIdParam === 'null' || $folderIdParam === '') ? null : (int) $folderIdParam;

        $this->entityManager->beginTransaction();
        try {
            foreach ($ids as $id) {
                $file = $this->fileRepository->find($id);
                if (!$file instanceof File) {
                    continue;
                }

                if ($file->isFolder()) {
                    $this->deleteFolderRecursively($file, false);
                } else {
                    $this->deleteFileAndStorage($file, false);
                }
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception) {
            $this->entityManager->rollback();
        }

        $allFiles = $this->getFilesInFolder($folderId);
        $path = $this->buildPath($folderId);

        $formatFile = function (File $file): array {
            return [
                'id'    => $file->getId(),
                'name'  => $file->getName(),
                'thumb' => $file->isFolder() ? null : $this->fileThumbnailService->getThumb($file),
                'url'   => $file->isFolder() ? null : $this->filePublicService->getUrlCreateIfMissing($file),
                'isDir' => $file->isFolder(),
                'key'   => $file->getKey(),
            ];
        };

        return [
            'files' => array_map($formatFile, $allFiles),
            'path'  => $path,
        ];
    }

    private function deleteFolderRecursively(File $folder, bool $flush = true): void
    {
        $children = $this->getFilesInFolder($folder->getId());
        foreach ($children as $child) {
            if ($child->isFolder()) {
                $this->deleteFolderRecursively($child, false);
            } else {
                $this->deleteFileAndStorage($child, false);
            }
        }
        $this->entityManager->remove($folder);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    private function deleteFileAndStorage(File $file, bool $flush = true): void
    {
        $this->filePublicService->deletePublicFiles($file);
        $this->fileStorageService->deleteFile($file);
        $this->fileThumbnailService->deleteThumbnails($file);

        $this->entityManager->remove($file);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    //

    private function buildPath(?int $folderId): array
    {
        $path = [];
        $currentId = $folderId;

        while ($currentId !== null) {
            $folder = $this->fileRepository->find($currentId);
            if (!$folder) {
                break;
            }
            $path = [$folder->getId() => $folder->getName()] + $path;
            $currentId = $folder->getFolder() ? $folder->getFolder()->getId() : null;
        }

        return $path;
    }

    private function getFilesInFolder(?int $folderId = null): array
    {
        $criteria = ['folder' => $folderId];

        return $this->fileRepository->findBy($criteria, ['isFolder' => 'DESC', 'name' => 'ASC']);
    }
}