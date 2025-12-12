<?php

namespace KikCMS\Domain\DataTable\Filter;

class DataTableFilters
{
    public ?string $search = null;
    public ?string $sort = null;
    public ?string $sortDirection = null;
    public int $page = 1;
    public array $filters = [];

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
}