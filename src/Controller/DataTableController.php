<?php

namespace App\Controller;

use App\Entity\DataTable\DataTableService;
use App\Entity\DataTable\Dto\DataTableEditDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class DataTableController extends AbstractController
{
    /** @var DataTableService */
    private DataTableService $dataTableService;

    /**
     * @param DataTableService $dataTableService
     */
    public function __construct(DataTableService $dataTableService)
    {
        $this->dataTableService = $dataTableService;
    }

    #[Route('/api/datatable/edit', methods: 'POST')]
    public function edit(#[MapRequestPayload] DataTableEditDto $dto): Response
    {
        $dataTable = $this->dataTableService->getByInstance($dto->getInstance());

        return new JsonResponse(['form' => $dataTable->getForm(), 'data' => []]);
    }
}