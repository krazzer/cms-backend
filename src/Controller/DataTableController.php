<?php

namespace App\Controller;

use App\Domain\DataTable\DataTableService;
use App\Domain\DataTable\Dto\DataTableCheckDto;
use App\Domain\DataTable\Dto\DataTableDeleteDto;
use App\Domain\DataTable\Dto\DataTableDto;
use App\Domain\DataTable\Dto\DataTableEditDto;
use App\Domain\DataTable\Dto\DataTableSaveDto;
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

    #[Route('/api/datatable/edit', methods: 'POST')]
    public function edit(#[MapRequestPayload] DataTableEditDto $dto): Response
    {
        $editData  = $this->dataTableService->getEditData($dto->getDataTable(), $dto->getId());

        if ( ! $editData) {
            $errorMessage = $this->translator->trans('dataTable.objectNotFound', ['id' => $dto->getId()]);
            return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['form' => $dto->getDataTable()->getForm(), 'data' => $editData]);
    }

    #[Route('/api/datatable/add', methods: 'POST')]
    public function add(#[MapRequestPayload] DataTableDto $dto): Response
    {
        $defaultData = $this->dataTableService->getDefaultData($dto->getDataTable());

        return new JsonResponse(['form' => $dto->getDataTable()->getForm(), 'data' => $defaultData]);
    }

    #[Route('/api/datatable/check', methods: 'POST')]
    public function check(#[MapRequestPayload] DataTableCheckDto $dto): Response
    {
        $this->dataTableService->updateCheckbox($dto->getDataTable(), $dto->getId(), $dto->getField(), $dto->getValue());

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/datatable/save', methods: 'POST')]
    public function save(#[MapRequestPayload] DataTableSaveDto $dto): Response
    {
        if ($dto->getId()) {
            $this->dataTableService->update($dto->getDataTable(), $dto->getId(), $dto->getData());
        } else {
            $id = $this->dataTableService->create($dto->getDataTable(), $dto->getData());
        }

        return new JsonResponse([
            'data' => $this->dataTableService->getData($dto->getDataTable()),
            'id'   => $id ?? $dto->getId()
        ]);
    }

    #[Route('/api/datatable/delete', methods: 'POST')]
    public function delete(#[MapRequestPayload] DataTableDeleteDto $dto): Response
    {
        $this->dataTableService->delete($dto->getDataTable(), $dto->getIds());

        return new JsonResponse(['data' => $this->dataTableService->getData($dto->getDataTable())]);
    }
}