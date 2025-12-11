<?php

namespace App\Domain\DataTable\Dto;

use App\Domain\DataTable\Filter\DataTableFilters;

class FilterDto extends Dto
{
    public DataTableFilters $filters;

    public function getFilters(): DataTableFilters
    {
        return $this->filters;
    }
}