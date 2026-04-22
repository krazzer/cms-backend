<?php

namespace KikCMS\Tests\Integration\Entity\Page;

use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageRepository;
use KikCMS\Tests\Integration\DbKernelTestCase;

class PageRepositoryTest extends DbKernelTestCase
{
    private PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = self::getContainer()->get(PageRepository::class);
    }

    public function testFindByMenu()
    {
        $this->addPage(1, [], 'main');
        $this->addPage(2, [1]);
        $this->addPage(3, [1]);
        $this->addPage(4, [1, 2]);
        $this->addPage(5, [1, 2]);

        // test 1 level
        $this->assertEquals([2, 3], $this->getPageIds($this->repository->findByMenuIdentifier()));

        // test 2 levels
        $this->assertEquals([2, 3, 4, 5], $this->getPageIds($this->repository->findByMenuIdentifier('main', 2)));
    }

    private function getPageIds(array $pages): array
    {
        return array_map(fn(Page $page) => $page->getId(), $pages);
    }

    private function addPage(int $id, array $parents, ?string $identifier = null): void
    {
        $page = new Page();
        $page->setId($id);
        $page->setParents($parents);

        if ($identifier) {
            $page->setIdentifier($identifier);
        }

        $this->em->persist($page);
        $this->em->flush();
    }
}