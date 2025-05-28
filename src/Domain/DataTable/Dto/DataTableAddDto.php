<?php

namespace App\Domain\DataTable\Dto;

class DataTableAddDto
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