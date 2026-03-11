<?php

namespace KikCMS\Entity\File;

use Doctrine\ORM\EntityManagerInterface;
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
        protected EntityManagerInterface $entityManager,
    ) {}

    #[Route('/api/media/upload')]
    public function uploadFile(#[MapUploadedFile] UploadedFile|array $files, Request $request): JsonResponse
    {
        $files = is_array($files) ? $files : [$files];
        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $folderId = $data['folder'] ?? null;

        try {
            $result = $this->fileService->uploadFiles($files, $folderId);
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan met het uploaden van het bestand.'], 500);
        }
    }

    #[Route('/api/media/newfolder', methods: ['POST'])]
    public function createFolder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $name = $data['name'] ?? null;
        $folderId = $data['folder'] ?? null;

        try {
            $result = $this->fileService->createFolder($name, $folderId);
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het aanmaken van de map.'], 500);
        }
    }
}