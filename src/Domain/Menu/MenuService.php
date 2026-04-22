<?php

namespace KikCMS\Domain\Menu;

use KikCMS\Domain\Frontend\FrontendConfig;
use KikCMS\Entity\Page\PageRepository;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class MenuService
{
    public function __construct(
        private PageRepository $pageRepository,
        private RequestStack $requestStack,
    ) {}

    public function get(string|int $id = FrontendConfig::MENU_MAIN): array
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        if (is_int($id)) {
            $pages = $this->pageRepository->findByMenuId($id);
        } else {
            $pages = $this->pageRepository->findByMenuIdentifier($id);
        }

        return array_map(fn($page) => [
            'name'    => $page->getName()[$locale],
            'url'     => '/' . $page->getSlug()[$locale],
            'content' => $page->getContent()[$locale] ?? null,
        ], $pages);
    }
}