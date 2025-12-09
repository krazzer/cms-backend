<?php

namespace App\Controller;

use App\Domain\DataTable\DataTableService;
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
        $dataTable = $this->dataTableService->getByInstance($dto->getInstance());
        $editData  = $this->dataTableService->getEditData($dto->getInstance(), $dto->getId());

        if ( ! $editData) {
            $errorMessage = $this->translator->trans('dataTable.objectNotFound', ['id' => $dto->getId()]);
            return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['form' => $dataTable->getForm(), 'data' => $editData]);
    }

    #[Route('/api/datatable/add', methods: 'POST')]
    public function add(#[MapRequestPayload] DataTableDto $dto): Response
    {
        $dataTable   = $this->dataTableService->getByInstance($dto->getInstance());
        $defaultData = $this->dataTableService->getDefaultData($dto->getInstance());

        return new JsonResponse(['form' => $dataTable->getForm(), 'data' => $defaultData]);
    }

    #[Route('/api/datatable/save', methods: 'POST')]
    public function save(#[MapRequestPayload] DataTableSaveDto $dto): Response
    {
        if ($dto->getId()) {
            $this->dataTableService->update($dto->getInstance(), $dto->getId(), $dto->getData());
        } else {
            $this->dataTableService->create($dto->getInstance(), $dto->getData());
        }

        return new JsonResponse($this->dataTableService->getData($dto->getInstance()));
    }

    #[Route('/api/datatable/delete', methods: 'POST')]
    public function delete(#[MapRequestPayload] DataTableDeleteDto $dto): Response
    {
        $this->dataTableService->delete($dto->getInstance(), $dto->getIds());

        return new JsonResponse($this->dataTableService->getData($dto->getInstance()));
    }
}