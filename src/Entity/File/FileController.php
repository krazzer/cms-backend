<?php

namespace KikCMS\Entity\File;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use KikCMS\Entity\File\Dto\ChangeKeyDto;
use KikCMS\Entity\File\Dto\ChangeNameDto;
use KikCMS\Entity\File\Dto\CreateDto;
use KikCMS\Entity\File\Dto\DeleteDto;
use KikCMS\Entity\File\Dto\OpenDto;
use KikCMS\Entity\File\Dto\PasteDto;
use KikCMS\Entity\File\Dto\SearchDto;
use KikCMS\Entity\File\Dto\UploadDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{
    public function __construct(
        private readonly FileService $fileService,
        protected EntityManagerInterface $entityManager,
    ) {}

    #[Route('/api/media/upload')]
    public function uploadFile(#[MapUploadedFile] UploadedFile|array $files, #[MapRequestPayload] UploadDto $dto): JsonResponse
    {
        $files = is_array($files) ? $files : [$files];

        try {
            $result = $this->fileService->uploadFiles($files, $dto->getFolder());
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan met het uploaden van het bestand.'], 500);
        }
    }

    #[Route('/api/media/newfolder', methods: ['POST'])]
    public function createFolder(#[MapRequestPayload] CreateDto $dto): JsonResponse
    {
        try {
            $result = $this->fileService->createFolder($dto->getName(), $dto->getFolderId());
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het aanmaken van de map.'], 500);
        }
    }

    #[Route('/api/media/open', methods: ['POST'])]
    public function openFolder(#[MapRequestPayload] OpenDto $dto): JsonResponse
    {
        try {
            $result = $this->fileService->openFolder($dto->getId());
            return $this->json($result);
        } catch (Exception $e) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het openen van de map.' . $e], 500);
        }
    }

    #[Route('/api/media/changefilename', methods: ['POST'])]
    public function changeFilename(#[MapRequestPayload] ChangeNameDto $dto): JsonResponse
    {
        try {
            $result = $this->fileService->changeFilename($dto->getName(), $dto->getId());
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het wijzigen van de bestandsnaam.'], 500);
        }
    }

    #[Route('/api/media/key', methods: ['POST'])]
    public function changeKey(#[MapRequestPayload] ChangeKeyDto $dto): JsonResponse
    {
        try {
            $result = $this->fileService->changeKey($dto->getName(), $dto->getId());
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het wijzigen van de sleutel.'], 500);
        }
    }

    #[Route('/api/media/delete', methods: ['POST'])]
    public function deleteFiles(#[MapRequestPayload] DeleteDto $dto): JsonResponse
    {
        try {
            $result = $this->fileService->deleteFiles($dto->getIds(), $dto->getFolderId());
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het verwijderen van bestanden.'], 500);
        }
    }

    #[Route('/api/media/paste', methods: ['POST'])]
    public function pasteFiles(#[MapRequestPayload] PasteDto $dto): JsonResponse
    {
        try {
            $result = $this->fileService->pasteFiles($dto->getIds(), $dto->getFolder());
            return $this->json($result);
        } catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het verplaatsen van bestanden.'], 500);
        }
    }

    #[Route('/api/media/search', methods: ['POST'])]
    public function search(#[MapRequestPayload] SearchDto $dto): JsonResponse
    {
        try {
            $result = $this->fileService->searchFiles($dto->getSearch());
            return $this->json($result);
        } catch (Exception) {
            return $this->json(['error' => 'Er is iets mis gegaan bij het zoeken.'], 500);
        }
    }
}