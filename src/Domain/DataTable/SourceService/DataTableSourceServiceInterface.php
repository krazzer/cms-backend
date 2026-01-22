<?php

namespace KikCMS\Domain\DataTable\SourceService;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;

interface DataTableSourceServiceInterface
{
    public function getData(DataTable $dataTable, ?Filters $filters = null, ?StoreData $storeData = null): array;

    public function getEditData(DataTable $dataTable, string $id, StoreData $storeData): array;

    public function create(DataTable $dataTable, array $createData, StoreData $storeData): int;

    public function update(DataTable $dataTable, string $id, array $updateData, StoreData $storeData): void;

    public function deleteList(DataTable $dataTable, array $ids, StoreData $storeData): void;

    public function updateCheckbox(DataTable $dataTable, int $id, string $field, bool $value): void;
}