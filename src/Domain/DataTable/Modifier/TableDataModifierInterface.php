<?php

namespace KikCMS\Domain\DataTable\Modifier;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;

interface TableDataModifierInterface extends DataTableModifierInterface
{
    public function modify(array $data, DataTable $dataTable, DataTableFilters $filters): array;
}