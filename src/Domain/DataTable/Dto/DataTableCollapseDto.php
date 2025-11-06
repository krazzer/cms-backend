<?php

namespace App\Domain\DataTable\Dto;

class DataTableCollapseDto extends DataTableDto
{
    public string $id;
    public bool $collapsed;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCollapsed(): bool
    {
        return $this->collapsed;
    }
}