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

    #[Route('/api/media/open', methods: ['POST'])]
    public function openFolder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $folderId = $data['id'] ?? null;

        try {
            $result = $this->fileService->openFolder($folderId);
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het openen van de map.'], 500);
        }
    }

    #[Route('/api/media/changefilename', methods: ['POST'])]
    public function changeFilename(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = $data['id'] ?? null;
        $newFileName = $data['name'] ?? null;

        if (!$id || !$newFileName) {
            return $this->json(['error' => 'Missing parameters'], 400);
        }

        try {
            $result = $this->fileService->changeFilename($newFileName, (int) $id);
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het wijzigen van de bestandsnaam.'], 500);
        }
    }

    #[Route('/api/media/key', methods: ['POST'])]
    public function changeKey(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = $data['id'] ?? null;
        $key = $data['name'] ?? null;

        if (!$id) {
            return $this->json(['error' => 'Missing file ID'], 400);
        }

        try {
            $result = $this->fileService->changeKey($key, (int) $id);
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het wijzigen van de sleutel.'], 500);
        }
    }

    #[Route('/api/media/delete', methods: ['POST'])]
    public function deleteFiles(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $ids = $data['ids'] ?? [];
        $folderId = $data['folder'] ?? null;

        if (empty($ids)) {
            return $this->json(['error' => 'Geen bestanden geselecteerd'], 400);
        }

        try {
            $result = $this->fileService->deleteFiles($ids, $folderId);
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het verwijderen van bestanden.'], 500);
        }
    }
}