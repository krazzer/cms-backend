<?php

namespace KikCMS\Entity\PageSection\Modifier;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Modifier\DataTableCellModifier;
use KikCMS\Domain\DataTable\Modifier\DataTableRowViewModifierInterface;
use KikCMS\Domain\DataTable\TableRow\TableViewRow;
use KikCMS\Entity\PageSection\PageSection;
use KikCMS\Entity\PageSection\PageSectionConfigService;

readonly class PageSectionRowViewModifier implements DataTableRowViewModifierInterface
{
    public function __construct(
        private PageSectionConfigService $pageSectionConfigService,
        private DataTableCellModifier $dataTableCellModifier
    ) {}

    public function modify(TableViewRow $tableViewRow, DataTable $dataTable, DataTableFilters $filters): TableViewRow
    {
        $sectionNameMap = $this->pageSectionConfigService->getSectionNameMap();

        $this->dataTableCellModifier->modify($tableViewRow, PageSection::FIELD_TYPE, function ($value) use ($sectionNameMap) {
            return $sectionNameMap[$value] ?? $value;
        });

        return $tableViewRow;
    }
}