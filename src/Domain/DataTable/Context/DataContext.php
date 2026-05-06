<?php

namespace KikCMS\Domain\DataTable\Context;

use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\DataTable;

class DataContext extends Context
{
    private array $data;
    private DataTable $dataTable;

    public function __construct(DataTable $dataTable, array $data = [])
    {
        $this->dataTable = $dataTable;
        $this->data      = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getDataTable(): DataTable
    {
        return $this->dataTable;
    }
}