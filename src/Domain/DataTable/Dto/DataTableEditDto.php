<?php

namespace App\Domain\DataTable\Dto;

class DataTableEditDto extends DataTableDto
{
    public string $id;

    public function getId(): string
    {
        return $this->id;
    }
}