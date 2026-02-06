<?php

namespace KikCMS\Domain\DataTable\SourceService;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\DataTableRowService;
use KikCMS\Domain\DataTable\Filter\DataTableLocalFilterService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;

readonly class LocalDataTableSourceService implements DataTableSourceServiceInterface
{
    public function __construct(
        private DataTableRowService $rowService,
        private DataTableLocalFilterService $filterService,
    ) {}

    public function update(DataTable $dataTable, Filters $filters, string $id, array $updateData, StoreData $storeData): void
    {
        $data = $storeData->getData();

        foreach ($data as &$row) {
            if ($row[DataTableConfig::ID] == $id) {
                $row = $updateData;
            }
        }

        $storeData->setData($data);
    }

    public function create(DataTable $dataTable, Filters $filters, array $createData, StoreData $storeData): int
    {
        $data  = $storeData->getData();
        $newId = $data ? max(array_column($data, DataTableConfig::ID)) + 1 : 1;

        $createData[DataTableConfig::ID] = $newId;

        $storeData->setData(array_merge($data, [$createData]));

        return $newId;
    }

    public function getData(DataTable $dataTable, Filters $filters, ?StoreData $storeData = null): array
    {
        $viewData = [];

        if ( ! $storeData) {
            return $viewData;
        }

        $filteredStoreData = $this->filterService->filter($storeData, $filters);

        foreach ($filteredStoreData as $row) {
            $viewDataRow = $this->rowService->getRowView($row, $dataTable, $filters, $row[DataTableConfig::ID]);
            $viewData[]  = $viewDataRow->toArray();
        }

        return $viewData;
    }

    public function getEditData(DataTable $dataTable, Filters $filters, int $id, StoreData $storeData): array
    {
        foreach ($storeData->getData() as $row) {
            if ($row[DataTableConfig::ID] == $id) {
                return $row;
            }
        }

        return [];
    }

    public function deleteList(DataTable $dataTable, array $ids, StoreData $storeData): void
    {
        $data = $storeData->getData();

        foreach ($data as $i => $row) {
            if (in_array($row[DataTableConfig::ID], $ids)) {
                unset($data[$i]);
            }
        }

        $storeData->setData($data);
    }

    public function updateCheckbox(DataTable $dataTable, Filters $filters, int $id, string $field, bool $value, StoreData $storeData): void
    {
        $data = $storeData->getData();

        foreach ($data as $i => $row) {
            if ($row[DataTableConfig::ID] == $id) {
                $data[$i][$field] = $value;
            }
        }

        $storeData->setData($data);
    }

    public function rearrange(DataTable $dataTable, int $source, int $target, Location $location, StoreData $storeData): void
    {
        $data = $storeData->getData();

        $ids = array_column($data, DataTableConfig::ID);

        $from = array_search($source, $ids);
        $to   = array_search($target, $ids);

        $item = $data[$from];

        unset($data[$from]);

        $data = array_values($data);

        array_splice($data, $to, 0, [$item]);

        $storeData->setData($data);
    }
}