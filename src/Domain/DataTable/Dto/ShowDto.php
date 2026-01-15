<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;

class ShowDto extends Dto
{
    public DataTableStoreData $storeData;

    public function getStoreData(): DataTableStoreData
    {
        return $this->storeData ?? new DataTableStoreData;
    }
}