<?php

namespace KikCMS\Entity\Page;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use KikCMS\Entity\Page\Path\PathService;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: 'prePersist', entity: Page::class)]
readonly class PageListener
{
    public function __construct(
        private SluggerInterface $slugger,
        private PathService $pathService,
        private PageTreeService $pageTreeService,
    ) {}

    public function prePersist(Page $page): void
    {
        if ($page->getSlug() === null) {
            $page->setSlug($this->getSlugs($page));
        }

        $this->pathService->updatePath($page);

        if($page->getDisplayOrder() === null){
            $maxDisplayOrder = $this->pageTreeService->getMaxDisplayOrder();
            $page->setDisplayOrder($maxDisplayOrder + 1);
        }
    }

    private function getSlugs(Page $page): array
    {
        if( ! $page->getName()){
            return [];
        }

        return array_map(function ($name) {
            return $this->slugger->slug($name)->lower()->toString();
        }, $page->getName());
    }
}