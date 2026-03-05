<?php

namespace KikCMS\Entity\File;

use KikCMS\Entity\File\Dto\UploadDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{
    /**
     * Todo: Ter info: deze methode heb ik aangepast, zodat de $files direct als UploadedFile array meekomen
     *
     * Ik heb ook een UploadDto toegevoegd, zodat ik de folder kan meegeven, als die is meegegeven, kun je direct de
     * folder als File object ophalen met $dto->getFolder().
     *
     * Ik heb ook de route aangepast zoals die in de frontend wordt aangeroepen (had je die niet getest?)
     *
     * Vue code is iets aangepast met andere naming (gebruik command kikcms:cms:update-admin om bij te werken)
     *
     * Todo: Services moeten altijd via de constructor (zie bijv in de DataTableController)
     */
    #[Route('/api/media/upload')]
    public function upload(#[MapUploadedFile] array $files, #[MapRequestPayload] UploadDto $dto, FileService $fileService): JsonResponse
    {
        /**
         * Todo: pas de code aan zodat het werkt met de nieuwe input.
         * Houd er ook regening mee dat meerdere bestanden geupload kunnen worden.
         */
        $uploadedFile = $request->files->get('file');
        if ( ! $uploadedFile) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        try {
            $folderId = $request->request->get('folder_id');
            $file     = $fileService->upload($uploadedFile, $folderId);
            $url      = $fileService->getUrlCreateIfMissing($file);

            /**
             * todo: Het is de bedoeling dat je alle bestanden van de huidige folder teruggeeft
             * Kijk in de Vue code wat er verwacht wordt
             */
            return $this->json([
                'id'   => $file->getId(),
                'name' => $file->getName(),
                'url'  => $url,
            ]);
        } catch (\Exception $e) {
            /**
             * Todo: Je geeft hier een error terug, maar in de Vue code is er niks die dit afhandeld
             * Daarnaast doe je $e->getMessage(), exception messages wil je eigenlijk nooit aan de gebruiker laten zien
             * vaak kunnen die daar niks mee en kan mogelijk ook een security issue zijn
             *
             * Beter is bijv: Er is iets mis gegaan met het uploaden van het bestand
             * Zorg dan ook dat deze melding in de Vue code wordt weergegeven
             */
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}