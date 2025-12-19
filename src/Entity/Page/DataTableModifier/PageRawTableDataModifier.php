<?php

namespace KikCMS\Entity\Page\DataTableModifier;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Modifier\RawTableDataModifierInterface;
use KikCMS\Entity\Page\PageTreeService;

readonly class PageRawTableDataModifier implements RawTableDataModifierInterface
{
    public function __construct(
        private PageTreeService $pageTreeService
    ) {}

    public function modify(array $data, DataTable $dataTable, DataTableFilters $filters): array
    {
        if ($filters->getSearch() || $filters->getSort()) {
            return $data;
        }

        $data = $this->pageTreeService->sort($data);
        return $this->pageTreeService->addHasChildren($data);
    }
}