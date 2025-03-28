<?php

namespace App\Entity\DataTable\Dto;

class DataTableEditDto
{
    /** @var string */
    public string $instance;

    /**
     * @return string
     */
    public function getInstance(): string
    {
        return $this->instance;
    }
}