<?php

namespace App\Domain\DataTable\Dto;

class DataTableDeleteDto extends DataTableDto
{
    public array $ids;

    public function getIds(): array
    {
        return $this->ids;
    }
}