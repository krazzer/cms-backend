<?php

namespace KikCMS\Domain\Frontend;

use KikCMS\Entity\Page\PageRepository;
use KikCMS\Entity\Page\Path\PathService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PathService $pathService,
        private readonly PageRepository $pageRepository,
    ) {}

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $locale = $request->getLocale();
        $page   = $this->pageRepository->findOneBy(['identifier' => FrontendConfig::DEFAULT_IDENTIFIER]);

        return $this->render('theme/templates/default.twig', [
            'title' => $page->getName()[$locale],
            'page'  => $page,
        ]);
    }

    #[Route('/{path}', name: 'page', requirements: ['path' => '[a-z0-9-/]+'], priority: -1)]
    public function page(string $path, Request $request): Response
    {
        $locale = $request->getLocale();

        if ( ! $page = $this->pathService->getPageByPath($path, $locale)) {
            throw $this->createNotFoundException();
        }

        return $this->render('theme/templates/default.twig', [
            'title' => $page->getName()[$locale],
            'page'  => $page,
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