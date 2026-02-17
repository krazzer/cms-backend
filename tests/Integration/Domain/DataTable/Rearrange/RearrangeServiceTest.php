<?php

namespace KikCMS\Tests\Integration\Domain\DataTable\Rearrange;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use KikCMS\Domain\DataTable\Rearrange\RearrangeService;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Entity\Page\Page;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

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

    public function testRearrangeBefore(): void
    {
        $page1 = $this->getPageWithOrder(1); // stays 1
        $page2 = $this->getPageWithOrder(2); // stays 2
        $page3 = $this->getPageWithOrder(3); // becom 4
        $page4 = $this->getPageWithOrder(4); // becom 5
        $page5 = $this->getPageWithOrder(5); // becom 3

        $this->service->rearrange($this->getDataTable(), $page5->getId(), $page3->getId(), Location::BEFORE);

        $this->em->refresh($page1);
        $this->em->refresh($page2);
        $this->em->refresh($page5);
        $this->em->refresh($page3);
        $this->em->refresh($page4);

        $this->assertEquals(1, $page1->getDisplayOrder());
        $this->assertEquals(2, $page2->getDisplayOrder());
        $this->assertEquals(3, $page5->getDisplayOrder());
        $this->assertEquals(4, $page3->getDisplayOrder());
        $this->assertEquals(5, $page4->getDisplayOrder());
    }

    public function testRearrangeBeforeFirst(): void
    {
        $page1 = $this->getPageWithOrder(1); // becomes 2
        $page2 = $this->getPageWithOrder(2); // becomes 3
        $page3 = $this->getPageWithOrder(3); // becomes 4
        $page4 = $this->getPageWithOrder(4); // becomes 5
        $page5 = $this->getPageWithOrder(5); // becomes 1

        $this->service->rearrange($this->getDataTable(), $page5->getId(), $page1->getId(), Location::BEFORE);

        $this->em->refresh($page1);
        $this->em->refresh($page2);
        $this->em->refresh($page5);
        $this->em->refresh($page3);
        $this->em->refresh($page4);

        $this->assertEquals(2, $page1->getDisplayOrder());
        $this->assertEquals(3, $page2->getDisplayOrder());
        $this->assertEquals(4, $page3->getDisplayOrder());
        $this->assertEquals(5, $page4->getDisplayOrder());
        $this->assertEquals(1, $page5->getDisplayOrder());
    }

    public function testRearrangeAfterLast(): void
    {
        $page1 = $this->getPageWithOrder(1); // becomes 5
        $page2 = $this->getPageWithOrder(2); // becomes 1
        $page3 = $this->getPageWithOrder(3); // becomes 2
        $page4 = $this->getPageWithOrder(4); // becomes 3
        $page5 = $this->getPageWithOrder(5); // becomes 4

        $this->service->rearrange($this->getDataTable(), $page1->getId(), $page5->getId(), Location::AFTER);

        $this->em->refresh($page1);
        $this->em->refresh($page2);
        $this->em->refresh($page5);
        $this->em->refresh($page3);
        $this->em->refresh($page4);

        $this->assertEquals(5, $page1->getDisplayOrder());
        $this->assertEquals(1, $page2->getDisplayOrder());
        $this->assertEquals(2, $page3->getDisplayOrder());
        $this->assertEquals(3, $page4->getDisplayOrder());
        $this->assertEquals(4, $page5->getDisplayOrder());
    }

    public function testRearrangeAfter(): void
    {
        $page1 = $this->getPageWithOrder(1); // becomes 1
        $page2 = $this->getPageWithOrder(2); // becomes 4
        $page3 = $this->getPageWithOrder(3); // becomes 2
        $page4 = $this->getPageWithOrder(4); // becomes 3
        $page5 = $this->getPageWithOrder(5); // becomes 5

        $this->service->rearrange($this->getDataTable(), $page2->getId(), $page4->getId(), Location::AFTER);

        $this->em->refresh($page1);
        $this->em->refresh($page2);
        $this->em->refresh($page5);
        $this->em->refresh($page3);
        $this->em->refresh($page4);

        $this->assertEquals(1, $page1->getDisplayOrder());
        $this->assertEquals(4, $page2->getDisplayOrder());
        $this->assertEquals(2, $page3->getDisplayOrder());
        $this->assertEquals(3, $page4->getDisplayOrder());
        $this->assertEquals(5, $page5->getDisplayOrder());
    }

    private function getDataTable(): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setPdoModel(Page::class);

        return $dataTable;
    }

    private function getPageWithOrder(int $order): Page
    {
        $entity = new Page();
        $entity->setDisplayOrder($order);
        $entity->setType('page');
        $entity->setCreatedAt(new DateTimeImmutable());
        $entity->setUpdatedAt(new DateTimeImmutable());

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
