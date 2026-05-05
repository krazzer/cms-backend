<?php

namespace KikCMS\Entity\Page\Path;

use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageRepository;

readonly class PathService
{
    public function __construct(private PageRepository $pageRepository) {}

    public function getPagesWithoutPath(): array
    {
        return $this->pageRepository->findBy(['path' => null]);
    }

    public function updateChildren(Page $page): array
    {
        $children = $this->pageRepository->findByParent($page);

        foreach ($children as $child) {
            $this->updatePath($child);
        }

        return $children;
    }

    public function updatePath(Page $page): bool
    {
        $parentIds = $page->getParents() ?: [];

        $parts = [];

        foreach ($parentIds as $id) {
            $parentPage = $this->pageRepository->find($id);

            foreach ($parentPage->getSlug() as $lang => $slug) {
                if ($slug) {
                    $parts[$lang][] = $slug;
                }
            }
        }

        if($page->getSlug()) {
            foreach ($page->getSlug() as $lang => $slug) {
                if ($slug) {
                    $parts[$lang][] = $slug;
                }
            }
        }

        $path = array_map(function ($slugs) {
            return implode('/', $slugs);
        }, $parts);

        if ($path) {
            $page->setPath($path);
            return true;
        }

        return false;
    }

    public function getPageByPath(string $path, string $lang): ?Page
    {
        $qb = $this->pageRepository->createQueryBuilder('p');

        $qb->andWhere("JSON_UNQUOTE(JSON_EXTRACT(p.path, :jsonPath)) = :path")
            ->setParameter('path', $path)
            ->setParameter('jsonPath', '$.' . $lang);

        return $qb->getQuery()->getOneOrNullResult();
    }
}