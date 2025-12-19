<?php

namespace KikCMS\Domain\DataTable\TableRow;

class TableViewRow
{
    public string $id;
    public array $rawRow;
    public array $filteredRow;

    public function __construct(string $id, array $rawRow, array $filteredRow)
    {
        $this->id          = $id;
        $this->rawRow      = $rawRow;
        $this->filteredRow = $filteredRow;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getRawRow(): array
    {
        return $this->rawRow;
    }

    public function setRawRow(array $rawRow): void
    {
        $this->rawRow = $rawRow;
    }

    public function getFilteredRow(): array
    {
        return $this->filteredRow;
    }

    public function setFilteredRow(array $filteredRow): void
    {
        $this->filteredRow = $filteredRow;
    }

    public function toArray(): array
    {
        return ['id' => $this->id, 'data' => $this->filteredRow];
    }
}