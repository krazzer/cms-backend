<?php

namespace KikCMS\Domain\Menu;

use KikCMS\Entity\Page\PageRepository;

readonly class MenuService
{
    public function __construct(
        private PageRepository $pageRepository
    ) {}

    public function get(?string $identifier = null): array
    {
        $identifier ??= 'main';

        $pages = $this->pageRepository->findByMenuIdentifier($identifier);

        return array_map(fn($page) => [
            'title' => $page->getTitle(),
            'url' => '/'.$page->getSlug(),
        ], $pages);
    }
}