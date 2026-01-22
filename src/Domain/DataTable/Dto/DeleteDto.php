<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;

class DeleteDto extends FilterDto
{
    public array $ids;
    public DataTableStoreData $storeData;

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getStoreData(): DataTableStoreData
    {
        return $this->storeData ?? new DataTableStoreData;
    }
}