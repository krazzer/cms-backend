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
use KikCMS\Entity\PageImage\PageImage;
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
            'content'         => null,
            'template'        => 'default',
            'type'            => 'page',
            'title'           => null,
            'header'          => null,
            'colors'          => null,
            'seo_title'       => null,
            'seo_keywords'    => null,
            'seo_description' => null,
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
        $this->assertEquals(['type' => 'page'], $responseData['data']);
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
        $dataTable = $this->dataTableService->getByInstance('page_images');

        // Must be null, nothing is created
        $pageImageId1 = $this->em->find(PageImage::class, 1);
        $this->assertNull($pageImageId1);

        $saveDto = new SaveDto();

        $saveDto->dataTable = $dataTable;
        $saveDto->filters   = new DataTableFilters()->setLangCode('nl');
        $saveDto->formData  = ['image_id' => 1];
        $saveDto->id        = null;

        $response = $this->controller->save($saveDto);

        $this->assertEquals(200, $response->getStatusCode());

        // Must not be null, new item is created
        $pageImageId1 = $this->em->find(PageImage::class, 1);
        $this->assertNotNull($pageImageId1);
    }

    public function testDelete()
    {
        $dataTable = $this->dataTableService->getByInstance('page_images');

        $pageImage = new PageImage();
        $pageImage->setId(1);
        $pageImage->setImageId(1);

        $this->em->persist($pageImage);
        $this->em->flush();

        // Must exist
        $pageImageId1 = $this->em->find(PageImage::class, 1);
        $this->assertNotNull($pageImageId1);

        $deleteDto = new DeleteDto();

        $deleteDto->dataTable = $dataTable;
        $deleteDto->filters   = new DataTableFilters()->setLangCode('nl');
        $deleteDto->ids       = [1];

        $response = $this->controller->delete($deleteDto);

        $this->assertEquals(200, $response->getStatusCode());

        // Must be null, item is deleted
        $pageImageId1 = $this->em->find(PageImage::class, 1);
        $this->assertNull($pageImageId1);
    }

    public function testRearrange()
    {
        $dataTable = $this->dataTableService->getByInstance('page_images');

        $pageImage1 = new PageImage();
        $pageImage1->setId(1);
        $pageImage1->setImageId(1);
        $pageImage1->setDisplayOrder(1);

        $pageImage2 = new PageImage();
        $pageImage2->setId(2);
        $pageImage2->setImageId(2);
        $pageImage2->setDisplayOrder(2);

        $pageImage3 = new PageImage();
        $pageImage3->setId(3);
        $pageImage3->setImageId(3);
        $pageImage3->setDisplayOrder(3);

        $this->em->persist($pageImage1);
        $this->em->persist($pageImage2);
        $this->em->persist($pageImage3);
        $this->em->flush();

        // Display order should be 1, 2, 3
        $pageImageId1 = $this->em->find(PageImage::class, 3);
        $this->assertEquals(3, $pageImageId1->getDisplayOrder());

        $rearrangeDto = new RearrangeDto();

        $rearrangeDto->dataTable = $dataTable;
        $rearrangeDto->filters   = new DataTableFilters()->setLangCode('nl');
        $rearrangeDto->source    = 3;
        $rearrangeDto->target    = 1;
        $rearrangeDto->location  = RearrangeLocation::BEFORE;

        $response = $this->controller->rearrange($rearrangeDto);

        $this->assertEquals(200, $response->getStatusCode());

        // Display order should be 1, 3, 2
        $pageImageId1 = $this->em->find(PageImage::class, 3);
        $this->assertEquals(1, $pageImageId1->getDisplayOrder());
    }
}