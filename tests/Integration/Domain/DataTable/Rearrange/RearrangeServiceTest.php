<?php

namespace KikCMS\Tests\Integration\Domain\DataTable\Rearrange;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Domain\DataTable\Rearrange\RearrangeService;
use KikCMS\Entity\PageImage\PageImage;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RearrangeServiceTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private RearrangeService $service;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em      = self::getContainer()->get(EntityManagerInterface::class);
        $this->service = self::getContainer()->get(RearrangeService::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata   = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    #[DataProvider('rearrangeProvider')]
    public function testRearrange(int $moveOrder, int $targetOrder, Location $location, array $expectedOrders): void
    {
        $pages = [];

        for ($i = 1; $i <= 5; $i++) {
            $pages[$i] = $this->getPageWithOrder($i);
        }

        $this->service->rearrange($this->getDataTable(), $pages[$moveOrder]->getId(), $pages[$targetOrder]->getId(),
            $location);

        foreach ($pages as $page) {
            $this->em->refresh($page);
        }

        foreach ($expectedOrders as $order => $expectedDisplayOrder) {
            $this->assertEquals($expectedDisplayOrder, $pages[$order]->getDisplayOrder());
        }
    }

    public static function rearrangeProvider(): array
    {
        return [
            'before middle' => [5, 3, Location::BEFORE, [1 => 1, 2 => 2, 3 => 4, 4 => 5, 5 => 3]],
            'before first'  => [5, 1, Location::BEFORE, [1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 1]],
            'after last'    => [1, 5, Location::AFTER, [1 => 5, 2 => 1, 3 => 2, 4 => 3, 5 => 4]],
            'after middle'  => [2, 4, Location::AFTER, [1 => 1, 2 => 4, 3 => 2, 4 => 3, 5 => 5]],
        ];
    }

    private function getDataTable(): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setPdoModel(PageImage::class);

        return $dataTable;
    }

    private function getPageWithOrder(int $order): PageImage
    {
        $entity = new PageImage();
        $entity->setDisplayOrder($order);
        $entity->setImageId(1);

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}