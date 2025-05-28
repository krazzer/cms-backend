<?php

namespace App\Domain\DataTable\Dto;

class DataTableSaveDto
{
    /** @var string */
    public string $instance;

    /** @var string|null */
    public string|null $id;

    /** @var array */
    public array $data;

    /**
     * @return string
     */
    public function getInstance(): string
    {
        return $this->instance;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}