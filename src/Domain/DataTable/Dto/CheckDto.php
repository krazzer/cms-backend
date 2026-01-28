<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;

class CheckDto extends FilterDto
{
    public string $field;
    public int $id;
    public bool $value;
    public DataTableStoreData $storeData;

    public function getField(): string
    {
        return $this->field;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    public function getStoreData(): DataTableStoreData
    {
        return $this->storeData;
    }
}