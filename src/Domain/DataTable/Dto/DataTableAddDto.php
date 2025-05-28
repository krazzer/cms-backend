<?php

namespace App\Domain\DataTable\Dto;

class DataTableAddDto
{
    public string $instance;

    public function getInstance(): string
    {
        return $this->instance;
    }
}