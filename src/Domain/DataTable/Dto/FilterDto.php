<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Filter\DataTableFilters;

class FilterDto extends Dto
{
    public DataTableFilters $filters;

    public function getFilters(): DataTableFilters
    {
        return $this->filters;
    }
}