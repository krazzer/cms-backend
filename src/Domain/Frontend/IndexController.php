<?php

namespace KikCMS\Domain\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    #[Route('/')]
    public function index(): Response
    {
        return $this->render('theme/templates/default.twig', [
            'title' => 'KikCMS',
        ]);
    }

    #[Route('/api/translations')]
    public function translations(): JsonResponse
    {
        return new JsonResponse([
            'translations' => $this->translator->getCatalogue()->all('frontend'),
        ]);
    }
}