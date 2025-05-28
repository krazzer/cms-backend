<?php

namespace App\Domain\DataTable\Dto;

class DataTableSaveDto extends DataTableDto
{
    public string|null $id;
    public array $data;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }
}