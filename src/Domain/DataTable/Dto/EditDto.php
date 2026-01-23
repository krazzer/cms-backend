<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;

class EditDto extends FilterDto
{
    public string $id;
    public DataTableStoreData $storeData;

    public function getId(): string
    {
        return $this->id;
    }

    public function getStoreData(): DataTableStoreData
    {
        return $this->storeData ?? new DataTableStoreData;
    }
}