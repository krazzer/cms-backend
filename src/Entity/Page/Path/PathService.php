<?php

namespace KikCMS\Entity\Page\Path;

use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageRepository;

readonly class PathService
{
    public function __construct(
        private PageRepository $pageRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getPagesWithoutPath(): array
    {
        return $this->pageRepository->findBy(['path' => null]);
    }

    public function updatePath(Page $page): bool
    {
        if ( ! $parentIds = $page->getParents()) {
            return false;
        }

        $parts = [];

        foreach ($parentIds as $id) {
            $parentPage = $this->pageRepository->find($id);

            foreach ($parentPage->getSlug() as $lang => $slug) {
                if ($slug) {
                    $parts[$lang][] = $slug;
                }
            }
        }

        foreach ($page->getSlug() as $lang => $slug) {
            if ($slug) {
                $parts[$lang][] = $slug;
            }
        }

        $path = array_map(function ($slugs) {
            return implode('/', $slugs);
        }, $parts);

        if ($path) {
            $page->setPath($path);

            $this->entityManager->persist($page);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }
}