<?php

namespace KikCMS\Controller;

use KikCMS\Entity\File\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{
    #[Route('/file/upload', name: 'file.upload', methods: ['POST'])]
    public function upload(Request $request, FileService $fileService): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if ( ! $uploadedFile) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        try {
            $folderId = $request->request->get('folder_id');
            $file     = $fileService->upload($uploadedFile, $folderId);
            $url      = $fileService->getUrlCreateIfMissing($file);

            return $this->json([
                'id'   => $file->getId(),
                'name' => $file->getName(),
                'url'  => $url,
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}