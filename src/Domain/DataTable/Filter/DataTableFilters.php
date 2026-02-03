<?php

namespace KikCMS\Domain\DataTable\Filter;

use KikCMS\Domain\DataTable\DataTable;

class DataTableFilters
{
    public ?string $search = null;
    public ?string $sort = null;
    public ?string $sortDirection = null;
    public string $langCode;
    public int $page = 1;
    public array $filters = [];
    public ?int $parentId = null;
    public ?DataTable $parentDataTable = null;

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): DataTableFilters
    {
        $this->search = $search;
        return $this;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function setSort(?string $sort): DataTableFilters
    {
        $this->sort = $sort;
        return $this;
    }

    public function getSortDirection(): ?string
    {
        return $this->sortDirection;
    }

    public function setSortDirection(?string $sortDirection): DataTableFilters
    {
        $this->sortDirection = $sortDirection;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): DataTableFilters
    {
        $this->page = $page;
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): DataTableFilters
    {
        $this->filters = $filters;
        return $this;
    }

    public function getLangCode(): string
    {
        return $this->langCode;
    }

    public function setLangCode(string $langCode): DataTableFilters
    {
        $this->langCode = $langCode;
        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): DataTableFilters
    {
        $this->parentId = $parentId;
        return $this;
    }

    public function getParentDataTable(): ?DataTable
    {
        return $this->parentDataTable;
    }

    public function setParentDataTable(?DataTable $parentDataTable): DataTableFilters
    {
        $this->parentDataTable = $parentDataTable;
        return $this;
    }
}