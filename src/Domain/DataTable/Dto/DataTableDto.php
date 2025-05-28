<?php

namespace App\Domain\DataTable\Dto;

class DataTableDto
{
    public string $instance;

    public function getInstance(): string
    {
        return $this->instance;
    }
}