<?php

namespace KikCMS\Controller;

use KikCMS\Domain\App\Exception\ObjectNotFoundException;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\DataTable\Dto\AddDto;
use KikCMS\Domain\DataTable\Dto\CheckDto;
use KikCMS\Domain\DataTable\Dto\DeleteDto;
use KikCMS\Domain\DataTable\Dto\EditDto;
use KikCMS\Domain\DataTable\Dto\FilterDto;
use KikCMS\Domain\DataTable\Dto\SaveDto;
use KikCMS\Domain\DataTable\Dto\ShowDto;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DataTableController extends AbstractController
{
    public function __construct(
        private readonly DataTableService $dataTableService,
        private readonly TranslatorInterface $translator,
    ) {}

    #[Route('/api/datatable', methods: 'POST')]
    public function show(#[MapRequestPayload] ShowDto $dto): Response
    {
        $dataTable = $dto->getDataTable();
        $instance  = $dataTable->getInstance();

        return new JsonResponse([
            'settings' => $this->dataTableService->getFullConfig($instance),
            'data'     => $this->dataTableService->getData($dataTable, new DataTableFilters, $dto->getStoreData()),
        ]);
    }

    #[Route('/api/datatable/edit', methods: 'POST')]
    public function edit(#[MapRequestPayload] EditDto $dto): Response
    {
        try {
            $editData = $this->dataTableService->getEditData($dto->getDataTable(), $dto->getId(), $dto->getStoreData());
        } catch (ObjectNotFoundException) {
            $errorMessage = $this->translator->trans('dataTable.objectNotFound', ['id' => $dto->getId()]);
            return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'form' => $dto->getDataTable()->getForm(),
            'data' => $editData ?? []
        ]);
    }

    #[Route('/api/datatable/add', methods: 'POST')]
    public function add(#[MapRequestPayload] AddDto $dto): Response
    {
        $defaultData = $this->dataTableService->getDefaultData($dto->getDataTable(), $dto->getType());
        $form        = $this->dataTableService->getForm($dto->getDataTable(), $dto->getType());

        return new JsonResponse(['form' => $form, 'data' => $defaultData]);
    }

    #[Route('/api/datatable/check', methods: 'POST')]
    public function check(#[MapRequestPayload] CheckDto $dto): Response
    {
        $this->dataTableService->updateCheckbox($dto->getDataTable(), $dto->getId(), $dto->getField(), $dto->getValue());

        return new JsonResponse(['success' => true]);
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

        $id       = $this->dataTableService->save($dataTable, $dto->getFormData(), $storeData, $dto->getId());
        $viewData = $this->dataTableService->getData($dataTable, $dto->getFilters(), $storeData);

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
        $viewData = $this->dataTableService->getData($dataTable, $dto->getFilters(), $storeData);

        return new JsonResponse([
            'data'      => $viewData,
            'storeData' => $storeData->getData(),
        ]);
    }
}