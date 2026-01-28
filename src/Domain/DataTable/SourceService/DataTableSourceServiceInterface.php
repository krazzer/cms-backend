<?php

namespace KikCMS\Domain\DataTable\SourceService;

use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;

interface DataTableSourceServiceInterface
{
    public function getData(DataTable $dataTable, Filters $filters, ?StoreData $storeData = null): array;

    public function getEditData(DataTable $dataTable, Filters $filters, string $id, StoreData $storeData): array;

    public function create(DataTable $dataTable, Filters $filters, array $createData, StoreData $storeData): int;

    public function update(DataTable $dataTable, Filters $filters, string $id, array $updateData, StoreData $storeData): void;

    public function deleteList(DataTable $dataTable, array $ids, StoreData $storeData): void;

    public function updateCheckbox(DataTable $dataTable, Filters $filters, int $id, string $field, bool $value, StoreData $storeData): void;

    public function rearrange(DataTable $dataTable, int $source, int $target, Location $location, StoreData $storeData);
}