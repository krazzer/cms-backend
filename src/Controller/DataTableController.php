<?php

namespace KikCMS\Controller;

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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class DataTableController extends AbstractController
{
    public function __construct(
        private readonly DataTableService $dataTableService,
    ) {}

    #[Route('/api/datatable', methods: 'POST')]
    public function show(#[MapRequestPayload] ShowDto $dto): Response
    {
        $dataTable = $dto->getDataTable();

        return new JsonResponse([
            'settings' => $this->dataTableService->getFullConfig($dataTable),
            'data'     => $this->dataTableService->getData($dataTable, new DataTableFilters, $dto->getStoreData()),
        ]);
    }

    #[Route('/api/datatable/edit', methods: 'POST')]
    public function edit(#[MapRequestPayload] EditDto $dto): Response
    {
        $dataTable = $dto->getDataTable();
        $storeData = $dto->getStoreData();

        $editData   = $this->dataTableService->getEditData($dataTable, $dto->getFilters(), $dto->getId(), $storeData);
        $helperData = $this->dataTableService->getSubDataTableHelperData($dataTable, $editData);

        return new JsonResponse([
            'form'       => $dataTable->getForm(),
            'data'       => $editData,
            'helperData' => $helperData,
        ]);
    }

    #[Route('/api/datatable/add', methods: 'POST')]
    public function add(#[MapRequestPayload] AddDto $dto): Response
    {
        $defaultData = $this->dataTableService->getDefaultData($dto->getDataTable(), $dto->getType());
        $form        = $this->dataTableService->getForm($dto->getDataTable(), $dto->getType());
        $helperData  = $this->dataTableService->getSubDataTableHelperData($dto->getDataTable());

        return new JsonResponse([
            'form'       => $form,
            'data'       => $defaultData,
            'helperData' => $helperData,
        ]);
    }

    #[Route('/api/datatable/check', methods: 'POST')]
    public function check(#[MapRequestPayload] CheckDto $dto): Response
    {
        $storeData = $dto->getStoreData();
        $dataTable = $dto->getDataTable();
        $filters   = $dto->getFilters();
        $field     = $dto->getField();
        $value     = $dto->getValue();
        $id        = $dto->getId();

        $this->dataTableService->updateCheckbox($dataTable, $filters, $id, $field, $value, $storeData);

        return new JsonResponse([
            'success'   => true,
            'storeData' => $storeData->getData(),
        ]);
    }

    #[Route('/api/datatable/filter', methods: 'POST')]
    public function filter(#[MapRequestPayload] FilterDto $dto): Response
    {
        return new JsonResponse(['data' => $this->dataTableService->getData($dto->getDataTable(), $dto->getFilters())]);
    }

    #[Route('/api/datatable/save', methods: 'POST')]
    public function save(#[MapRequestPayload] SaveDto $dto): Response
    {
        $storeData = $dto->getStoreData();
        $dataTable = $dto->getDataTable();
        $formData  = $dto->getFormData();
        $filters   = $dto->getFilters();

        $id       = $this->dataTableService->save($dataTable, $filters, $formData, $storeData, $dto->getId());
        $viewData = $this->dataTableService->getData($dataTable, $filters, $storeData);

        return new JsonResponse([
            'data'      => $viewData,
            'storeData' => $storeData->getData(),
            'editedId'  => $id,
        ]);
    }

    #[Route('/api/datatable/delete', methods: 'POST')]
    public function delete(#[MapRequestPayload] DeleteDto $dto): Response
    {
        $storeData = $dto->getStoreData();
        $dataTable = $dto->getDataTable();

        $this->dataTableService->delete($dataTable, $dto->getIds(), $storeData);

        return new JsonResponse([
            'data'      => $this->dataTableService->getData($dataTable, $dto->getFilters(), $storeData),
            'storeData' => $storeData->getData(),
        ]);
    }

    #[Route('/api/datatable/rearrange', methods: 'POST')]
    public function rearrange(#[MapRequestPayload] RearrangeDto $dto): Response
    {
        $storeData = $dto->getStoreData();
        $dataTable = $dto->getDataTable();
        $source    = $dto->getSource();
        $target    = $dto->getTarget();
        $location  = $dto->getLocation();
        $filters   = $dto->getFilters();

        $this->dataTableService->rearrange($dataTable, $source, $target, $location, $storeData);

        return new JsonResponse([
            'data'      => $this->dataTableService->getData($dataTable, $filters, $storeData),
            'storeData' => $storeData->getData(),
        ]);
    }
}