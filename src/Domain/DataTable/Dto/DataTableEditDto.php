<?php

namespace App\Domain\DataTable\Dto;

class DataTableEditDto
{
    /** @var string */
    public string $instance;

    /** @var string */
    public string $id;

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
}