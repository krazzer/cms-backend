<?php

namespace KikCMS\Tests\Integration\Domain\DataTable\Tree;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Domain\DataTable\Tree\TreeRearrangeService;
use KikCMS\Entity\Page\Page;
use KikCMS\Tests\Integration\DbKernelTestCase;

class TreeRearrangeServiceTest extends DbKernelTestCase
{
    private TreeRearrangeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = self::getContainer()->get(TreeRearrangeService::class);
    }

    public function testRearrangeInsideConflict(): void
    {
        $pages = $this->buildTree();

        $id20 = $this->getPageIdByIdentifier(20);
        $id23 = $this->getPageIdByIdentifier(23);

        // should not be possible to move a parent into its own child
        $this->service->rearrange($this->getDataTable(), $id20, $id23, Location::INSIDE);

        $expectedOrders = [
            // parents
            10 => 10, 20 => 20,
            // children of parent 10
            11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15,
            // children of parent 20
            21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25,
        ];

        $this->assertEquals($expectedOrders, $this->getActualOrderMap($pages));
    }

    public function testRearrangeAfterInAnotherParent(): void
    {
        $pages = $this->buildTree();

        $id13 = $this->getPageIdByIdentifier(13);
        $id23 = $this->getPageIdByIdentifier(23);

        $this->service->rearrange($this->getDataTable(), $id13, $id23, Location::AFTER);

        $expectedOrders = [
            // parents
            10 => 10, 20 => 20,
            // children of parent 10
            11 => 11, 12 => 12, 14 => 13, 15 => 14,
            // children of parent 20
            21 => 21, 22 => 22, 23 => 23, 13 => 24, 24 => 25, 25 => 26,
        ];

        $this->assertEquals($expectedOrders, $this->getActualOrderMap($pages));
    }

    public function testRearrangeBeforeInAnotherParent(): void
    {
        $pages = $this->buildTree();

        $id13 = $this->getPageIdByIdentifier(13);
        $id23 = $this->getPageIdByIdentifier(23);

        $this->service->rearrange($this->getDataTable(), $id13, $id23, Location::BEFORE);

        $expectedOrders = [
            // parents
            10 => 10, 20 => 20,
            // children of parent 10
            11 => 11, 12 => 12, 14 => 13, 15 => 14,
            // children of parent 20
            21 => 21, 22 => 22, 13 => 23, 23 => 24, 24 => 25, 25 => 26,
        ];

        $this->assertEquals($expectedOrders, $this->getActualOrderMap($pages));
    }

    public function testRearrangeBeforeWithSameParent(): void
    {
        $pages = $this->buildTree();

        $id11 = $this->getPageIdByIdentifier(11);
        $id15 = $this->getPageIdByIdentifier(15);

        $this->service->rearrange($this->getDataTable(), $id11, $id15, Location::BEFORE);

        $expectedOrders = [
            // parents
            10 => 10, 20 => 20,
            // children of parent 10
            11 => 14, 12 => 11, 13 => 12, 14 => 13, 15 => 15,
            // children of parent 20
            21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25,
        ];

        $this->assertEquals($expectedOrders, $this->getActualOrderMap($pages));
    }

    public function testRearrangeAfterWithSameParent(): void
    {
        $pages = $this->buildTree();

        $id11 = $this->getPageIdByIdentifier(11);
        $id15 = $this->getPageIdByIdentifier(15);

        $this->service->rearrange($this->getDataTable(), $id11, $id15, Location::AFTER);

        $expectedOrders = [
            // parents
            10 => 10, 20 => 20,
            // children of parent 10
            11 => 15, 12 => 11, 13 => 12, 14 => 13, 15 => 14,
            // children of parent 20
            21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25,
        ];

        $this->assertEquals($expectedOrders, $this->getActualOrderMap($pages));
    }

    public function testRearrangeInside(): void
    {
        $pages = $this->buildTree();

        $id13 = $this->getPageIdByIdentifier(13);
        $id20 = $this->getPageIdByIdentifier(20);

        $this->service->rearrange($this->getDataTable(), $id13, $id20, Location::INSIDE);

        $expectedOrders = [
            // parents
            10 => 10, 20 => 20,
            // children of parent 10
            11 => 11, 12 => 12, 14 => 13, 15 => 14,
            // children of parent 20
            21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 13 => 26
        ];

        $this->assertEquals($expectedOrders, $this->getActualOrderMap($pages));
    }

    private function buildTree(): array
    {
        $page1Children = [];
        $page2Children = [];

        $parent1 = $this->getPage(10, 10);
        $parent2 = $this->getPage(20, 20);

        for ($i = 1; $i <= 5; $i++) {
            $page1Children[$i] = $this->getPage(10 + $i, 10 + $i, $parent1->getId());
        }

        for ($i = 1; $i <= 5; $i++) {
            $page2Children[$i] = $this->getPage(20 + $i, 20 + $i, $parent2->getId());
        }

        return [$parent1, $parent2, ...$page1Children, ...$page2Children];
    }

    private function getDataTable(): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setPdoModel(Page::class);

        return $dataTable;
    }

    private function getPageIdByIdentifier(int $identifier): int
    {
        return $this->em->getRepository(Page::class)->findOneBy(['identifier' => $identifier])->getId();
    }

    private function getPage(int $order, ?int $id = null, ?int $parent = null): Page
    {
        $entity = new Page();
        $entity->setDisplayOrder($order);

        if ($id) {
            $entity->setIdentifier($id);
        }

        if ($parent) {
            $entity->setParents([$parent]);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    private function getActualOrderMap($pages): array
    {
        $orderMap = [];

        foreach ($pages as $page) {
            $this->em->refresh($page);
            $orderMap[$page->getIdentifier()] = $page->getDisplayOrder();
        }

        return $orderMap;
    }

}