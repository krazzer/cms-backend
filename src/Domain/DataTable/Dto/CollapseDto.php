<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\DataTable;

class CollapseDto extends Dto
{
    public string $id;
    public bool $collapsed;

    public function __construct(DataTable $dataTable, string $id, bool $collapsed)
    {
        $this->id        = $id;
        $this->collapsed = $collapsed;
        $this->dataTable = $dataTable;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCollapsed(): bool
    {
        return $this->collapsed;
    }
}