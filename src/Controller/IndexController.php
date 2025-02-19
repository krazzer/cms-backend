<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    /** @var TranslatorInterface */
    private TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    #[Route('/')]
    public function index(): Response
    {
        return new Response(
            '<html lang=""><body>This is the index. Yet to be filled!</body></html>'
        );
    }

    #[Route('/api/translations')]
    public function translations(): JsonResponse
    {
        return new JsonResponse($this->translator->getCatalogue()->all('frontend'));
    }
}