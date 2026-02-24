<?php

namespace KikCMS\Tests\Integration\Domain\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use KikCMS\Controller\DataTableController;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\DataTable\Dto\ShowDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DataTableControllerTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private DataTableController $controller;
    private DataTableService $dataTableService;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em               = self::getContainer()->get(EntityManagerInterface::class);
        $this->controller       = self::getContainer()->get(DataTableController::class);
        $this->dataTableService = self::getContainer()->get(DataTableService::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata   = $this->em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testShow()
    {
        $dataTable = $this->dataTableService->getByInstance('pages');

        $showDto = new ShowDto();

        $showDto->dataTable = $dataTable;

        $response     = $this->controller->show($showDto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $responseData['data']);
    }
}