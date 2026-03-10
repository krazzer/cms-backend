<?php

namespace KikCMS\Entity\File;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{
    public function __construct(
        private readonly FileService $fileService,
        private readonly FilePublicService $filePublicService,
        private readonly FileRepository $fileRepository,
    ) {}

    #[Route('/api/media/upload')]
    public function upload(#[MapUploadedFile] UploadedFile|array $files, Request $request): JsonResponse
    {
        $files = is_array($files) ? $files : [$files];

        if (empty($files)) {
            return $this->json(['error' => 'Geen bestanden geüpload'], 400);
        }

        try {
            $folderId = $request->request->get('folder');
            $folderId = ($folderId === 'null' || $folderId === '') ? null : (int) $folderId;
            $folder   = $folderId ? $this->fileRepository->find($folderId) : null;

            $newFiles = [];

            foreach ($files as $uploadedFile) {
                $fileEntity = $this->fileService->upload($uploadedFile, $folder);
                $newFiles[] = $fileEntity;
            }

            $allFiles = $this->fileService->getFilesInFolder($folderId);

            $formatFile = function (File $file): array {
                return [
                    'id'   => $file->getId(),
                    'name' => $file->getName(),
                    'url'  => $this->filePublicService->getUrlCreateIfMissing($file),
                ];
            };

            return $this->json([
                'files'    => array_map($formatFile, $allFiles),
                'newFiles' => array_map($formatFile, $newFiles),
            ]);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan met het uploaden van het bestand.'], 500);
        }
    }
}