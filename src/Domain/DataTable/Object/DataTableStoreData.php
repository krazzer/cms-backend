<?php

namespace KikCMS\Domain\DataTable\Object;

class DataTableStoreData
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): DataTableStoreData
    {
        $this->data = $data;
        return $this;
    }
}