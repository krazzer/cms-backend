<?php

namespace KikCMS\Tests\Integration\Domain\DataTable\Rearrange;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Rearrange\RearrangeIntegrityService;
use KikCMS\Entity\PageImage\PageImage;
use KikCMS\Tests\Integration\DbKernelTestCase;

class RearrangeIntegrityServiceTest extends DbKernelTestCase
{
    private RearrangeIntegrityService $service;

    const string ENTITY_CLASS = PageImage::class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = self::getContainer()->get(RearrangeIntegrityService::class);
    }

    public function testHasDoubles(): void
    {
        // no doubles
        $this->createEntitiesWithOrders([1, 2, 3, 4, 5]);
        $this->assertFalse($this->service->hasDoubles(self::ENTITY_CLASS));

        // has doubles
        $this->createEntitiesWithOrders([1, 1, 2, 3, 4, 5]);
        $this->assertTrue($this->service->hasDoubles(self::ENTITY_CLASS));
    }

    public function testCheck(): void
    {
        // no doubles, no change
        $this->createEntitiesWithOrders([1, 2, 3, 4, 5]);
        $this->service->check(self::ENTITY_CLASS);
        $this->assertEquals([1, 2, 3, 4, 5], $this->getDisplayOrders());

        // has doubles, change orders
        $this->createEntitiesWithOrders([1, 2, 2, 3, 4, 5, 9, 100]);
        $this->service->check(self::ENTITY_CLASS);
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $this->getDisplayOrders());
    }

    private function createEntitiesWithOrders(array $orders): void
    {
        $this->deleteEntities();

        foreach ($orders as $order) {
            $this->createEntityWithOrder($order);
        }
    }

    private function createEntityWithOrder(int $order): void
    {
        $entity = new (self::ENTITY_CLASS)();
        $entity->setDisplayOrder($order);
        $entity->setImageId(1);

        $this->em->persist($entity);
        $this->em->flush();
    }

    private function deleteEntities(): void
    {
        $this->em->createQueryBuilder()->delete(self::ENTITY_CLASS, 'e')->getQuery()->execute();
    }

    public function getDisplayOrders(): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('e.' . DataTableConfig::DISPLAY_ORDER)
            ->from(self::ENTITY_CLASS, 'e')
            ->orderBy('e.' . DataTableConfig::DISPLAY_ORDER);

        return $query->getQuery()->getSingleColumnResult();
    }
}