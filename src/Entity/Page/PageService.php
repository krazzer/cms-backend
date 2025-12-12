<?php

namespace KikCMS\Entity\Page;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
readonly class PageService
{
    public function __construct(private PageTreeService $pageTreeService) {}

    /** @noinspection PhpUnusedParameterInspection */
    public function modifyDataTableOutput(array $data, DataTable $dataTable, DataTableFilters $filters): array
    {
        if($filters->getSearch() || $filters->getSort()) {
            return $data;
        }

        $data = $this->pageTreeService->sort($data);
        return $this->pageTreeService->addHasChildren($data);
    }
}