<?php

namespace App\Controller;

use App\Entity\DataTable\DataTableService;
use App\Entity\DataTable\Dto\DataTableAddDto;
use App\Entity\DataTable\Dto\DataTableEditDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DataTableController extends AbstractController
{
    /** @var DataTableService */
    private DataTableService $dataTableService;

    /** @var TranslatorInterface */
    private TranslatorInterface $translator;

    /**
     * @param DataTableService $dataTableService
     * @param TranslatorInterface $translator
     */
    public function __construct(DataTableService $dataTableService, TranslatorInterface $translator)
    {
        $this->dataTableService = $dataTableService;
        $this->translator = $translator;
    }

    #[Route('/api/datatable/edit', methods: 'POST')]
    public function edit(#[MapRequestPayload] DataTableEditDto $dto): Response
    {
        $dataTable = $this->dataTableService->getByInstance($dto->getInstance());
        $editData  = $this->dataTableService->getEditData($dto->getInstance(), $dto->getId());

        if( ! $editData){
            $errorMessage = $this->translator->trans('dataTable.objectNotFound', ['id' => $dto->getId()]);
            return new JsonResponse(['error' => $errorMessage], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['form' => $dataTable->getForm(), 'data' => $editData]);
    }

    #[Route('/api/datatable/add', methods: 'POST')]
    public function add(#[MapRequestPayload] DataTableAddDto $dto): Response
    {
        $dataTable = $this->dataTableService->getByInstance($dto->getInstance());

        return new JsonResponse(['form' => $dataTable->getForm()]);
    }
}