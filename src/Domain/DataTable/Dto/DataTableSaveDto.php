<?php

namespace App\Domain\DataTable\Dto;

class DataTableSaveDto
{
    public string $instance;
    public string|null $id;
    public array $data;

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }
}