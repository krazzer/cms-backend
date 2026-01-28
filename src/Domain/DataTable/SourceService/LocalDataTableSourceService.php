<?php

namespace KikCMS\Domain\DataTable\SourceService;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\DataTableRowService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;

readonly class LocalDataTableSourceService implements DataTableSourceServiceInterface
{
    public function __construct(
        private DataTableRowService $rowService,
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
        $data = $storeData->getData();

        $newId = $data ? max(array_keys($data)) + 1 : 1;

        $data[$newId] = $createData;

        $storeData->setData($data);

        return $newId;
    }

    public function getData(DataTable $dataTable, Filters $filters, ?StoreData $storeData = null): array
    {
        $viewData = [];

        if ( ! $storeData) {
            return $viewData;
        }

        foreach ($storeData->getData() as $row) {
            $viewDataRow = $this->rowService->getRowView($row, $dataTable, $filters, $row[DataTableConfig::ID]);
            $viewData[]  = $viewDataRow->toArray();
        }

        return $viewData;
    }

    public function getEditData(DataTable $dataTable, Filters $filters, string $id, StoreData $storeData): array
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

        foreach ($ids as $id) {
            unset($data[$id]);
        }

        $storeData->setData($data);
    }

    public function updateCheckbox(DataTable $dataTable, Filters $filters, int $id, string $field, bool $value): void
    {
        // TODO: Implement updateCheckbox() method.
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