<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;

class SaveDto extends FilterDto
{
    public string|null|int $id;
    public array $formData;
    public DataTableStoreData $storeData;

    public function getId(): string|null|int
    {
        return $this->id;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getStoreData(): DataTableStoreData
    {
        return $this->storeData ?? new DataTableStoreData;
    }
}