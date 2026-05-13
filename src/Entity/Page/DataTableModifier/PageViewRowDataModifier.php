<?php

namespace KikCMS\Entity\Page\DataTableModifier;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Modifier\DataTableRowViewModifierInterface;
use KikCMS\Domain\DataTable\TableRow\TableViewRow;
use KikCMS\Domain\DataTable\Tree\CollapseService;
use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageTableViewRow;

readonly class PageViewRowDataModifier implements DataTableRowViewModifierInterface
{
    public function __construct(
        private CollapseService $collapseService
    ) {}

    public function modify(TableViewRow $tableViewRow, DataTable $dataTable, DataTableFilters $filters): TableViewRow
    {
        if ($filters->getSearch() || $filters->getSort()) {
            return $tableViewRow;
        }

        $rawRow = $tableViewRow->getRawRow();
        $level  = count($rawRow[Page::FIELD_PARENTS] ?? []);

        $tableViewRow = new PageTableViewRow($tableViewRow)
            ->setLevel($level)
            ->setChildren($rawRow[Page::FIELD_CHILDREN])
            ->setType($rawRow[Page::FIELD_TYPE]);

        if ($tableViewRow->getChildren()) {
            $isCollapsed = $this->collapseService->isCollapsed($tableViewRow->getId(), $dataTable->getInstance());
            $tableViewRow->setCollapsed($isCollapsed);
        }

        return $tableViewRow;
    }
}