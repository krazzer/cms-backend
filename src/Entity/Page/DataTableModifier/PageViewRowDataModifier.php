<?php

namespace KikCMS\Entity\Page\DataTableModifier;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Modifier\ViewRowDataModifierInterface;
use KikCMS\Domain\DataTable\TableRow\TableViewRow;
use KikCMS\Domain\DataTable\Tree\CollapseService;
use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageTableViewRow;

readonly class PageViewRowDataModifier implements ViewRowDataModifierInterface
{
    public function __construct(
        private CollapseService $collapseService
    ) {}

    public function modify(TableViewRow $viewRow, DataTable $dataTable, DataTableFilters $filters): TableViewRow
    {
        if ($filters->getSearch() || $filters->getSort()) {
            return $viewRow;
        }

        $rawRow = $viewRow->getRawRow();
        $level  = count($rawRow[Page::FIELD_PARENTS] ?? []);

        $viewRow = new PageTableViewRow($viewRow)
            ->setLevel($level)
            ->setChildren($rawRow[Page::FIELD_CHILDREN])
            ->setType($rawRow[Page::FIELD_TYPE]);

        if ($viewRow->getChildren()) {
            $isCollapsed = $this->collapseService->isCollapsed($viewRow->getId(), $dataTable->getInstance());
            $viewRow->setCollapsed($isCollapsed);
        }

        return $viewRow;
    }
}