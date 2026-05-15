<?php

namespace KikCMS\Domain\Frontend;

use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageRepository;
use KikCMS\Entity\Page\Path\PathService;
use KikCMS\Entity\Page\Renderer\GlobalVariables\GlobalVariableResolver;
use KikCMS\Entity\Page\Renderer\PageRendererResolver;
use KikCMS\Entity\Page\Renderer\RenderType;
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
        private readonly PageRendererResolver $pageRendererResolver,
        private readonly GlobalVariableResolver $globalVariableResolver,
    ) {}

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        if ( ! $page = $this->pageRepository->findOneBy(['identifier' => FrontendConfig::DEFAULT_IDENTIFIER])) {
            throw $this->createNotFoundException();
        }

        return $this->resolveByPage($page, $request);
    }

    #[Route('/api/translations')]
    public function translations(): JsonResponse
    {
        return new JsonResponse([
            'translations' => $this->translator->getCatalogue()->all('frontend'),
        ]);
    }

    #[Route('/{path}', name: 'page', requirements: ['path' => '[a-z0-9-/]+'], priority: -1)]
    public function page(string $path, Request $request): Response
    {
        if ( ! $page = $this->pathService->getPageByPath($path, $request->getLocale())) {
            throw $this->createNotFoundException();
        }

        return $this->resolveByPage($page, $request);
    }

    private function resolveByPage(Page $page, Request $request): Response
    {
        $result  = $this->pageRendererResolver->resolve($page)->render($page, $request);
        $globals = $this->globalVariableResolver->resolve($request, $page);

        return match ($result->type) {
            RenderType::VIEW => $this->render($result->template, array_replace_recursive($globals, $result->context)),
            RenderType::RESPONSE => $result->response,
        };
    }
}