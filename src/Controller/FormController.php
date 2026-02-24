<?php

declare(strict_types=1);

namespace KikCMS\Controller;

use KikCMS\Domain\Form\Dto\SaveDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class FormController extends AbstractController
{
    #[Route('/api/form/save')]
    public function save(#[MapRequestPayload] SaveDto $dto): Response
    {
        dlog($dto);

        return new JsonResponse(['ok' => true]);
    }
}
