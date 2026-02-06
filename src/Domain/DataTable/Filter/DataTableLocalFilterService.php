<?php

namespace KikCMS\Domain\DataTable\Filter;


use KikCMS\Domain\DataTable\Config\DataTableConfig as Config;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;

readonly class DataTableLocalFilterService
{
    public function __construct() {}

    public function filter(StoreData $storeData, DataTable $dataTable, Filters $filters): array
    {
        $filteredData = $storeData->getData();

        if ($filters->getSort() && $filters->getSortDirection()) {
            $filteredData = $this->sort($filteredData, $filters);
        }

        if ($filters->getSearch()) {
            $filteredData = $this->search($filteredData, $filters, $dataTable);
        }

        return $filteredData;
    }

    private function sort(array $data, Filters $filters): array
    {
        usort($data, function ($a, $b) use ($filters) {
            $aValue = $a[$filters->getSort()] ?? null;
            $bValue = $b[$filters->getSort()] ?? null;

            $result = $aValue <=> $bValue;
            return ($filters->getSortDirection() === Config::SORT_ASC) ? $result : -$result;
        });

        return $data;
    }

    private function search(array $data, Filters $filters, DataTable $dataTable): array
    {
        $columns = $dataTable->getSearchColumns();
        $keyword = $filters->getSearch();

        return array_values(array_filter($data, function ($item) use ($columns, $keyword) {
            return array_any($columns, fn($field) => isset($item[$field]) &&
                stripos((string) $item[$field], $keyword) !== false);
        }));
    }
}