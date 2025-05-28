<?php

namespace App\Domain\DataTable\Dto;

class DataTableEditDto
{
    public string $instance;
    public string $id;

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getId(): string
    {
        return $this->id;
    }
}