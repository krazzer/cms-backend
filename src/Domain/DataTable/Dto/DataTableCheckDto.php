<?php

namespace App\Domain\DataTable\Dto;

class DataTableCheckDto extends DataTableDto
{
    public string $field;
    public int $id;
    public bool $value;

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
}