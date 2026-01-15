<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;

class EditDto extends Dto
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