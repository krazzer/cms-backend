<?php

namespace KikCMS\Tests\Integration\Domain\Controller;

use DateTimeImmutable;
use KikCMS\Domain\DataTable\Controller\DataTableController;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\DataTable\Dto\AddDto;
use KikCMS\Domain\DataTable\Dto\CheckDto;
use KikCMS\Domain\DataTable\Dto\DeleteDto;
use KikCMS\Domain\DataTable\Dto\EditDto;
use KikCMS\Domain\DataTable\Dto\FilterDto;
use KikCMS\Domain\DataTable\Dto\RearrangeDto;
use KikCMS\Domain\DataTable\Dto\SaveDto;
use KikCMS\Domain\DataTable\Dto\ShowDto;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation;
use KikCMS\Entity\Page\Page;
use KikCMS\Entity\PageSection\PageSection;
use KikCMS\Tests\Integration\DbKernelTestCase;

class DataTableControllerTest extends DbKernelTestCase
{
    private DataTableController $controller;
    private DataTableService $dataTableService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller       = self::getContainer()->get(DataTableController::class);
        $this->dataTableService = self::getContainer()->get(DataTableService::class);
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

    public function testEdit()
    {
        $dataTable = $this->dataTableService->getByInstance('pages');

        $page = new Page();
        $page->setId(1);
        $page->setType('page');
        $page->setCreatedAt(new DateTimeImmutable());
        $page->setUpdatedAt(new DateTimeImmutable());
        $page->setActive(['nl' => true]);
        $page->setTemplate('default');

        $this->em->persist($page);
        $this->em->flush();

        $editDto          = new EditDto();
        $editDto->filters = new DataTableFilters()->setLangCode('nl');
        $editDto->id      = 1;

        $editDto->dataTable = $dataTable;

        $response     = $this->controller->edit($editDto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'active'          => true,
            'slug'            => null,
            'template'        => 'default',
            'title'           => null,
            'seo_title'       => null,
            'seo_keywords'    => null,
            'seo_description' => null,
            'identifier'      => null
        ], $responseData['data']);
    }

    public function testAdd()
    {
        $dataTable = $this->dataTableService->getByInstance('pages');

        $addDto = new AddDto();

        $addDto->dataTable = $dataTable;

        $response     = $this->controller->add($addDto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $responseData['data']);
    }

    public function testCheck()
    {
        $page = new Page();
        $page->setId(1);
        $page->setType('page');
        $page->setCreatedAt(new DateTimeImmutable());
        $page->setUpdatedAt(new DateTimeImmutable());
        $page->setActive(['nl' => false]);
        $page->setTemplate('default');

        $this->em->persist($page);
        $this->em->flush();

        $dataTable = $this->dataTableService->getByInstance('pages');

        $checkDto = new CheckDto();

        $checkDto->filters   = new DataTableFilters()->setLangCode('nl');
        $checkDto->dataTable = $dataTable;
        $checkDto->field     = 'active.*';
        $checkDto->value     = true;
        $checkDto->id        = 1;

        $response = $this->controller->check($checkDto);

        $this->assertEquals(200, $response->getStatusCode());

        $updatedPage = $this->em->find(Page::class, 1);

        $this->assertEquals(['nl' => true], $updatedPage->getActive());
    }

    public function testFilter()
    {
        $dataTable = $this->dataTableService->getByInstance('pages');

        $filterDto = new FilterDto();

        $filterDto->dataTable = $dataTable;
        $filterDto->filters   = new DataTableFilters()->setLangCode('nl');

        $response = $this->controller->filter($filterDto);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
    {
        $dataTable = $this->dataTableService->getByInstance('page_content');
        $pagesDataTable = $this->dataTableService->getByInstance('pages');

        // Must be null, nothing is created
        $pageSectionId1 = $this->em->find(PageSection::class, 1);
        $this->assertNull($pageSectionId1);

        $saveDto = new SaveDto();

        $saveDto->dataTable = $dataTable;
        $saveDto->filters   = new DataTableFilters()->setLangCode('nl')->setParentId(1)->setParentDataTable($pagesDataTable);
        $saveDto->formData  = ['type' => 'richtext'];
        $saveDto->id        = null;

        $response = $this->controller->save($saveDto);

        $this->assertEquals(200, $response->getStatusCode());

        // Must not be null, new item is created
        $pageSectionId1 = $this->em->find(PageSection::class, 1);
        $this->assertNotNull($pageSectionId1);
    }

    public function testDelete()
    {
        $dataTable = $this->dataTableService->getByInstance('page_content');

        $page = new Page();
        $page->setId(1);

        $this->em->persist($page);
        $this->em->flush();

        $section = new PageSection();
        $section->setId(1);
        $section->setType('type');
        $section->setPage($page);

        $this->em->persist($section);
        $this->em->flush();

        // Must exist
        $section = $this->em->find(PageSection::class, 1);
        $this->assertNotNull($section);

        $deleteDto = new DeleteDto();

        $deleteDto->dataTable = $dataTable;
        $deleteDto->filters   = new DataTableFilters()->setLangCode('nl');
        $deleteDto->ids       = [1];

        $response = $this->controller->delete($deleteDto);

        $this->assertEquals(200, $response->getStatusCode());

        // Must be null, item is deleted
        $section = $this->em->find(PageSection::class, 1);
        $this->assertNull($section);
    }

    public function testRearrange()
    {
        $dataTable = $this->dataTableService->getByInstance('page_content');

        $page = new Page();
        $page->setId(1);

        $this->em->persist($page);
        $this->em->flush();

        $pageImage1 = new PageSection();
        $pageImage1->setId(1);
        $pageImage1->setType('x');
        $pageImage1->setPage($page);
        $pageImage1->setDisplayOrder(1);

        $pageImage2 = new PageSection();
        $pageImage2->setId(2);
        $pageImage2->setType('x');
        $pageImage2->setPage($page);
        $pageImage2->setDisplayOrder(2);

        $pageImage3 = new PageSection();
        $pageImage3->setId(3);
        $pageImage3->setType('x');
        $pageImage3->setPage($page);
        $pageImage3->setDisplayOrder(3);

        $this->em->persist($pageImage1);
        $this->em->persist($pageImage2);
        $this->em->persist($pageImage3);
        $this->em->flush();

        // Display order should be 1, 2, 3
        $section = $this->em->find(PageSection::class, 3);
        $this->assertEquals(3, $section->getDisplayOrder());

        $rearrangeDto = new RearrangeDto();

        $rearrangeDto->dataTable = $dataTable;
        $rearrangeDto->filters   = new DataTableFilters()->setLangCode('nl');
        $rearrangeDto->source    = 3;
        $rearrangeDto->target    = 1;
        $rearrangeDto->location  = RearrangeLocation::BEFORE;

        $response = $this->controller->rearrange($rearrangeDto);

        $this->assertEquals(200, $response->getStatusCode());

        // Display order should be 1, 3, 2
        $section = $this->em->find(PageSection::class, 3);
        $this->assertEquals(1, $section->getDisplayOrder());
    }
}