<?php

namespace KikCMS\Tests\Integration\Domain\Controller;

use DateTimeImmutable;
use KikCMS\Domain\DataTable\Controller\DataTableTreeController;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\DataTable\Dto\CollapseDto;
use KikCMS\Domain\DataTable\Dto\RearrangeDto;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation;
use KikCMS\Domain\DataTable\Tree\CollapseService;
use KikCMS\Entity\Page\Page;
use KikCMS\Tests\Integration\DbKernelTestCase;

class DataTableTreeControllerTest extends DbKernelTestCase
{
    private DataTableTreeController $controller;
    private DataTableService $dataTableService;
    private CollapseService $collapseService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller       = self::getContainer()->get(DataTableTreeController::class);
        $this->dataTableService = self::getContainer()->get(DataTableService::class);
        $this->collapseService  = self::getContainer()->get(CollapseService::class);
    }

    public function testCollapse()
    {
        $this->createPage(1, null);
        $this->createPage(2, 1);

        // is not collapsed yet
        $this->assertFalse($this->collapseService->isCollapsed(1, 'pages'));

        $dataTable = $this->dataTableService->getByInstance('pages');

        $response = $this->controller->collapse(new CollapseDto($dataTable, 1, true));

        $this->assertEquals(200, $response->getStatusCode());

        // is now collapsed
        $this->assertTrue($this->collapseService->isCollapsed(1, 'pages'));
    }

    public function testRearrange()
    {
        $dataTable = $this->dataTableService->getByInstance('pages');

        $this->createPage(1, null, 1);
        $this->createPage(2, null, 2);
        $this->createPage(3, null, 3);

        // Display order should be 1, 2, 3
        $page1 = $this->em->find(Page::class, 3);
        $this->assertEquals(3, $page1->getDisplayOrder());

        $rearrangeDto = new RearrangeDto();

        $rearrangeDto->dataTable = $dataTable;
        $rearrangeDto->filters   = new DataTableFilters()->setLangCode('nl');
        $rearrangeDto->source    = 3;
        $rearrangeDto->target    = 1;
        $rearrangeDto->location  = RearrangeLocation::BEFORE;

        $response = $this->controller->rearrange($rearrangeDto);

        $this->assertEquals(200, $response->getStatusCode());

        // Display order should be 1, 3, 2
        $pageImageId1 = $this->em->find(Page::class, 3);
        $this->assertEquals(1, $pageImageId1->getDisplayOrder());
    }

    private function createPage(int $id, ?int $parentId, ?int $displayOrder = null): void
    {
        $page = new Page();
        $page->setId($id);
        $page->setParents($parentId ? [$parentId] : null);
        $page->setType('page');
        $page->setCreatedAt(new DateTimeImmutable());
        $page->setUpdatedAt(new DateTimeImmutable());
        $page->setActive(['nl' => true]);
        $page->setTemplate('default');

        if ($displayOrder) {
            $page->setDisplayOrder($displayOrder);
        }

        $this->em->persist($page);
        $this->em->flush();
    }
}