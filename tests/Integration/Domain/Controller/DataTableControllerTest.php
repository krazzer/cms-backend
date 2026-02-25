<?php

namespace KikCMS\Tests\Integration\Domain\Controller;

use KikCMS\Domain\DataTable\Controller\DataTableController;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\DataTable\Delete\DeleteImpactCalculator;
use KikCMS\Domain\DataTable\Delete\DeleteImpactMessageBuilder;
use KikCMS\Domain\DataTable\Dto\AddDto;
use KikCMS\Domain\DataTable\Dto\CheckDto;
use KikCMS\Domain\DataTable\Dto\DeleteDto;
use KikCMS\Domain\DataTable\Dto\EditDto;
use KikCMS\Domain\DataTable\Dto\FilterDto;
use KikCMS\Domain\DataTable\Dto\RearrangeDto;
use KikCMS\Domain\DataTable\Dto\SaveDto;
use KikCMS\Domain\DataTable\Dto\ShowDto;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation;
use KikCMS\Domain\Form\Form;
use KikCMS\Domain\Form\FormService;
use PHPUnit\Framework\TestCase;

class DataTableControllerTest extends TestCase
{
    private DataTableService $dataTableService;
    private DeleteImpactCalculator $deleteImpactCalculator;
    private DeleteImpactMessageBuilder $deleteImpactMessageBuilder;
    private FormService $formService;
    private DataTableController $controller;

    protected function setUp(): void
    {
        $this->dataTableService         = $this->createMock(DataTableService::class);
        $this->deleteImpactCalculator   = $this->createMock(DeleteImpactCalculator::class);
        $this->deleteImpactMessageBuilder = $this->createMock(DeleteImpactMessageBuilder::class);
        $this->formService              = $this->createMock(FormService::class);

        $this->controller = new DataTableController(
            $this->dataTableService,
            $this->deleteImpactCalculator,
            $this->deleteImpactMessageBuilder,
            $this->formService,
        );
    }

    public function testShow(): void
    {
        $dataTable  = $this->getDataTable();
        $storeData  = new DataTableStoreData(['offset' => 10]);
        $fullConfig = ['headers' => ['title']];
        $data       = [['id' => 1]];

        $dto = new ShowDto();
        $dto->dataTable = $dataTable;
        $dto->storeData = $storeData;

        $this->dataTableService->expects($this->once())
            ->method('getFullConfig')
            ->with($dataTable)
            ->willReturn($fullConfig);

        $this->dataTableService->expects($this->once())
            ->method('getData')
            ->with($dataTable, $this->isInstanceOf(DataTableFilters::class), $storeData)
            ->willReturn($data);

        $response     = $this->controller->show($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['settings' => $fullConfig, 'data' => $data], $responseData);
    }

    public function testEdit(): void
    {
        $dataTable   = $this->getDataTable();
        $filters     = new DataTableFilters();
        $storeData   = new DataTableStoreData(['q' => 'x']);
        $editData    = ['id' => 4, 'title' => 'Title'];
        $helperData  = ['subTable' => ['rows' => []]];
        $formConfig  = ['tabs' => []];

        $dto = new EditDto();
        $dto->dataTable = $dataTable;
        $dto->filters = $filters;
        $dto->storeData = $storeData;
        $dto->id = 4;

        $this->dataTableService->expects($this->once())
            ->method('getEditData')
            ->with($dataTable, $filters, 4, $storeData)
            ->willReturn($editData);

        $this->dataTableService->expects($this->once())
            ->method('getSubDataTableHelperData')
            ->with($dataTable, 4, $editData)
            ->willReturn($helperData);

        $this->formService->expects($this->once())
            ->method('getFullConfig')
            ->with($dataTable->getForm())
            ->willReturn($formConfig);

        $response     = $this->controller->edit($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'form'       => $formConfig,
            'data'       => $editData,
            'helperData' => $helperData,
        ], $responseData);
    }

    public function testAdd(): void
    {
        $dataTable    = $this->getDataTable();
        $defaultData  = ['title' => 'new'];
        $helperData   = ['related' => ['rows' => []]];
        $formConfig   = ['fields' => []];
        $typeForm     = $this->createMock(Form::class);

        $dto = new AddDto();
        $dto->dataTable = $dataTable;
        $dto->type = 'special';

        $this->dataTableService->expects($this->once())
            ->method('getDefaultData')
            ->with($dataTable, 'special')
            ->willReturn($defaultData);

        $this->dataTableService->expects($this->once())
            ->method('getForm')
            ->with($dataTable, 'special')
            ->willReturn($typeForm);

        $this->dataTableService->expects($this->once())
            ->method('getSubDataTableHelperData')
            ->with($dataTable)
            ->willReturn($helperData);

        $this->formService->expects($this->once())
            ->method('getFullConfig')
            ->with($typeForm)
            ->willReturn($formConfig);

        $response     = $this->controller->add($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'form'       => $formConfig,
            'data'       => $defaultData,
            'helperData' => $helperData,
        ], $responseData);
    }

    public function testCheck(): void
    {
        $dataTable = $this->getDataTable();
        $filters   = new DataTableFilters();
        $storeData = new DataTableStoreData(['foo' => 'bar']);

        $dto = new CheckDto();
        $dto->dataTable = $dataTable;
        $dto->filters = $filters;
        $dto->storeData = $storeData;
        $dto->id = 7;
        $dto->field = 'active';
        $dto->value = true;

        $this->dataTableService->expects($this->once())
            ->method('updateCheckbox')
            ->with($dataTable, $filters, 7, 'active', true, $storeData);

        $response     = $this->controller->check($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success'   => true,
            'storeData' => ['foo' => 'bar'],
        ], $responseData);
    }

    public function testFilter(): void
    {
        $dataTable = $this->getDataTable();
        $filters   = new DataTableFilters();
        $storeData = new DataTableStoreData(['page' => 2]);
        $data      = [['id' => 22]];

        $dto = new FilterDto();
        $dto->dataTable = $dataTable;
        $dto->filters = $filters;
        $dto->storeData = $storeData;

        $this->dataTableService->expects($this->once())
            ->method('getData')
            ->with($dataTable, $filters, $storeData)
            ->willReturn($data);

        $response     = $this->controller->filter($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['data' => $data], $responseData);
    }

    public function testSave(): void
    {
        $dataTable = $this->getDataTable();
        $filters   = new DataTableFilters();
        $storeData = new DataTableStoreData(['sort' => 'title']);
        $formData  = ['title' => 'Saved'];
        $viewData  = [['id' => 101, 'title' => 'Saved']];

        $dto = new SaveDto();
        $dto->dataTable = $dataTable;
        $dto->filters = $filters;
        $dto->storeData = $storeData;
        $dto->id = null;
        $dto->formData = $formData;

        $this->dataTableService->expects($this->once())
            ->method('save')
            ->with($dataTable, $filters, $formData, $storeData, null)
            ->willReturn(101);

        $this->dataTableService->expects($this->once())
            ->method('getData')
            ->with($dataTable, $filters, $storeData)
            ->willReturn($viewData);

        $response     = $this->controller->save($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'data'      => $viewData,
            'storeData' => ['sort' => 'title'],
            'editedId'  => 101,
        ], $responseData);
    }

    public function testDeleteReturnsConfirmationWhenRequired(): void
    {
        $dataTable   = $this->getDataTable();
        $filters     = new DataTableFilters();

        $dto = new DeleteDto();
        $dto->dataTable = $dataTable;
        $dto->filters = $filters;
        $dto->ids = [1, 2];

        $deletePlan = ['Some\\Entity' => 3];

        $this->deleteImpactCalculator->expects($this->once())
            ->method('inspect')
            ->with($dataTable, [1, 2])
            ->willReturn($deletePlan);

        $this->deleteImpactMessageBuilder->expects($this->once())
            ->method('build')
            ->with($deletePlan)
            ->willReturn('confirm message');

        $this->dataTableService->expects($this->never())->method('delete');

        $response     = $this->controller->delete($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['confirm' => 'confirm message'], $responseData);
    }

    public function testDeleteRemovesRowsWhenConfirmed(): void
    {
        $dataTable = $this->getDataTable();
        $filters   = new DataTableFilters();
        $storeData = new DataTableStoreData(['f' => 1]);
        $viewData  = [['id' => 1]];

        $dto = new DeleteDto();
        $dto->dataTable = $dataTable;
        $dto->filters = $filters;
        $dto->storeData = $storeData;
        $dto->ids = [1];
        $dto->confirmed = true;

        $this->deleteImpactCalculator->expects($this->once())
            ->method('inspect')
            ->with($dataTable, [1])
            ->willReturn(['Some\\Entity' => 1]);

        $this->deleteImpactMessageBuilder->expects($this->never())->method('build');

        $this->dataTableService->expects($this->once())
            ->method('delete')
            ->with($dataTable, $filters, [1], $storeData);

        $this->dataTableService->expects($this->once())
            ->method('getData')
            ->with($dataTable, $filters, $storeData)
            ->willReturn($viewData);

        $response     = $this->controller->delete($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'data'      => $viewData,
            'storeData' => ['f' => 1],
        ], $responseData);
    }

    public function testRearrange(): void
    {
        $dataTable = $this->getDataTable();
        $filters   = new DataTableFilters();
        $storeData = new DataTableStoreData(['collapsed' => [3]]);
        $viewData  = [['id' => 10]];

        $dto = new RearrangeDto();
        $dto->dataTable = $dataTable;
        $dto->filters = $filters;
        $dto->storeData = $storeData;
        $dto->source = 10;
        $dto->target = 20;
        $dto->location = RearrangeLocation::BEFORE;

        $this->dataTableService->expects($this->once())
            ->method('rearrange')
            ->with($dataTable, $filters, 10, 20, RearrangeLocation::BEFORE, $storeData);

        $this->dataTableService->expects($this->once())
            ->method('getData')
            ->with($dataTable, $filters, $storeData)
            ->willReturn($viewData);

        $response     = $this->controller->rearrange($dto);
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'data'      => $viewData,
            'storeData' => ['collapsed' => [3]],
        ], $responseData);
    }

    private function getDataTable(): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setForm(new Form());

        return $dataTable;
    }
}
