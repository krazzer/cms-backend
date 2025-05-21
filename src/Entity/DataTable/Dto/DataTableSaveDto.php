<?php

namespace App\Entity\DataTable\Dto;

class DataTableSaveDto
{
    /** @var string */
    public string $instance;

    /** @var string */
    public string $id;

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
     * @return string
     */
    public function getId(): string
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