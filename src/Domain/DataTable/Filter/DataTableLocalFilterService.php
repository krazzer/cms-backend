<?php

namespace KikCMS\Domain\DataTable\Filter;


use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Object\DataTableStoreData;

readonly class DataTableLocalFilterService
{
    public function __construct() {}

    public function filter(DataTableStoreData $storeData, DataTableFilters $filters): array
    {
        $filteredData = $storeData->getData();

        if ($filters->getSort() && $filters->getSortDirection()) {
            $filteredData = $this->sort($filteredData, $filters);
        }

        return $filteredData;
    }

    private function sort(array $data, DataTableFilters $filters): array
    {
        usort($data, function ($a, $b) use ($filters) {
            $aValue = $a[$filters->getSort()] ?? null;
            $bValue = $b[$filters->getSort()] ?? null;

            $result = $aValue <=> $bValue;
            return ($filters->getSortDirection() === DataTableConfig::SORT_ASC) ? $result : -$result;
        });

        return $data;
    }
}