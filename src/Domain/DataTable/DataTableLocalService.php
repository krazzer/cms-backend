<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;

readonly class DataTableLocalService
{
    public function update(string $id, array $updateData, DataTableStoreData $storeData): void
    {
        $data = $storeData->getData();

        $data[$id] = $updateData;

        $storeData->setData($data);
    }

    public function create(array $createData, DataTableStoreData $storeData): int
    {
        $data = $storeData->getData();

        $newId = $data ? max(array_keys($data)) + 1 : 1;

        $data[$newId] = $createData;

        $storeData->setData($data);

        return $newId;
    }
}