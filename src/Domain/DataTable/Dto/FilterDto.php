<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData;

class FilterDto extends Dto
{
    public DataTableFilters $filters;
    public DataTableStoreData $storeData;

    public function getFilters(): DataTableFilters
    {
        return $this->filters;
    }

    public function getStoreData(): DataTableStoreData
    {
        return $this->storeData ?? new DataTableStoreData();
    }
}