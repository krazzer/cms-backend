<?php

namespace App\Controller;

use App\Domain\DataTable\DataTableService;
use App\Domain\DataTable\Dto\CollapseDto;
use App\Domain\DataTable\Dto\RearrangeDto;
use App\Domain\DataTable\Tree\CollapseService;
use App\Domain\DataTable\Tree\RearrangeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class DataTableTreeController extends AbstractController
{
    public function __construct(
        private readonly CollapseService $collapseService,
        private readonly RearrangeService $rearrangeService,
        private readonly DataTableService $dataTableService,
    ) {}

    #[Route('/api/datatable/collapse', methods: 'POST')]
    public function collapse(#[MapRequestPayload] CollapseDto $dto): Response
    {
        $this->collapseService->setByDto($dto);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/datatable/page/rearrange', methods: 'POST')]
    public function rearrange(#[MapRequestPayload] RearrangeDto $dto): Response
    {
        $this->rearrangeService->rearrange($dto->getDataTable(), $dto->getSource(), $dto->getTarget(), $dto->getLocation());

        return new JsonResponse($this->dataTableService->getData($dto->getDataTable()));
    }
}