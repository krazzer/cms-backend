<?php

namespace KikCMS\Domain\DataTable\Modifier;

use KikCMS\Domain\DataTable\TableRow\TableViewRow;

class DataTableCellModifier
{
    public function modify(TableViewRow $tableViewRow, string $key, callable $callable): void
    {
        $row = $tableViewRow->getFilteredRow();

        $row[$key] = $callable($row[$key], $row);

        $tableViewRow->setFilteredRow($row);
    }
}