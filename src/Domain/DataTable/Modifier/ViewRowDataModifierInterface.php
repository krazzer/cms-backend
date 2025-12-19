<?php

namespace KikCMS\Domain\DataTable\Modifier;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\TableRow\TableViewRow;

interface ViewRowDataModifierInterface extends DataTableModifierInterface
{
    public function modify(TableViewRow $viewRow, DataTable $dataTable, DataTableFilters $filters): TableViewRow;
}