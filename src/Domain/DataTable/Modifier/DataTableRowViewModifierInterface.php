<?php

namespace KikCMS\Domain\DataTable\Modifier;

use KikCMS\Domain\App\Modifier\ModifierInterface;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\TableRow\TableViewRow;

interface DataTableRowViewModifierInterface extends ModifierInterface
{
    public function modify(TableViewRow $tableViewRow, DataTable $dataTable, DataTableFilters $filters): TableViewRow;
}