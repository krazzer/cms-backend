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

        // should not be possible to move a parent into its own child
        $this->service->rearrange($this->getDataTable(), 20, 23, Location::INSIDE);

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

        $this->service->rearrange($this->getDataTable(), 13, 23, Location::AFTER);

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

        $this->service->rearrange($this->getDataTable(), 13, 23, Location::BEFORE);

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

        $this->service->rearrange($this->getDataTable(), 11, 15, Location::BEFORE);

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

        $this->service->rearrange($this->getDataTable(), 11, 15, Location::AFTER);

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

        $this->service->rearrange($this->getDataTable(), 13, 20, Location::INSIDE);

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

    private function getPage(int $order, ?int $id = null, ?int $parent = null): Page
    {
        $entity = new Page();
        $entity->setDisplayOrder($order);

        if ($id) {
            $entity->setId($order);
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
            $orderMap[$page->getId()] = $page->getDisplayOrder();
        }

        return $orderMap;
    }

}