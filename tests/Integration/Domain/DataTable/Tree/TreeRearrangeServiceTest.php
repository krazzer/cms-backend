<?php

namespace KikCMS\Tests\Integration\Domain\DataTable\Tree;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use KikCMS\Domain\DataTable\Tree\TreeRearrangeService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TreeRearrangeServiceTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private TreeRearrangeService $service;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em      = self::getContainer()->get(EntityManagerInterface::class);
        $this->service = self::getContainer()->get(TreeRearrangeService::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata   = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}