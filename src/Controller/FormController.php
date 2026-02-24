<?php

declare(strict_types=1);

namespace KikCMS\Controller;

use KikCMS\Domain\Form\Dto\SaveDto;
use KikCMS\Domain\Form\Source\SourceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class FormController extends AbstractController
{
    public function __construct(
        private readonly SourceService $storageService
    ) {}

    #[Route('/api/form/save')]
    public function save(#[MapRequestPayload] SaveDto $dto): Response
    {
        $this->storageService->store($dto->getForm(), $dto->getData());

        return new JsonResponse(['success' => true]);
    }
}
