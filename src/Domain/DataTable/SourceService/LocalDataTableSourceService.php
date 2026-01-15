<?php

namespace KikCMS\Domain\DataTable\SourceService;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\DataTableRowService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;

readonly class LocalDataTableSourceService implements DataTableSourceServiceInterface
{
    public function __construct(
        private DataTableRowService $rowService,
    ) {}

    public function update(DataTable $dataTable, string $id, array $updateData, StoreData $storeData): void
    {
        $data = $storeData->getData();

        $data[$id] = $updateData;

        $storeData->setData($data);
    }

    public function create(DataTable $dataTable, array $createData, StoreData $storeData): int
    {
        $data = $storeData->getData();

        $newId = $data ? max(array_keys($data)) + 1 : 1;

        $data[$newId] = $createData;

        $storeData->setData($data);

        return $newId;
    }

    public function getData(DataTable $dataTable, ?Filters $filters = null, ?StoreData $storeData = null): array
    {
        $viewData = [];

        if( ! $storeData){
            return $viewData;
        }

        foreach ($storeData->getData() as $id => $row) {
            $viewDataRow = $this->rowService->getRowView($row + ['id' => $id], $dataTable, $filters, $id);
            $viewData[]  = $viewDataRow->toArray();
        }

        return $viewData;
    }

    public function getEditData(DataTable $dataTable, string $id, StoreData $storeData): ?array
    {
        return $storeData->getData()[$id];
    }

    public function deleteList(DataTable $dataTable, array $ids, StoreData $storeData): void
    {
        $data = $storeData->getData();

        foreach ($ids as $id) {
            unset($data[$id]);
        }

        $storeData->setData($data);
    }

    public function updateCheckbox(DataTable $dataTable, int $id, string $field, bool $value): void
    {
        // TODO: Implement updateCheckbox() method.
    }
}