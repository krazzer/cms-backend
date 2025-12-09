<?php

namespace App\Domain\DataTable;

use App\Domain\DataTable\Field\FieldService;

readonly class DataTableStoreService
{
    public function __construct(
        private FieldService $fieldService,
        private DataTableConfigService $dataTableConfigService,
    ) {}

    public function getDataArrayToStore(DataTable $dataTable, array $rawData): array
    {
        $storeData = [];

        $fields = $this->fieldService->getFieldMap($dataTable);

        foreach ($fields as $key => $field) {
            if ( ! array_key_exists($key, $rawData)) {
                continue;
            }

            $value = $rawData[$key];
            $field = $field->getField();

            $fieldWithValue = $this->dataTableConfigService->convertPathToArray($field, $value, $dataTable->getLangCode());

            $storeData = array_replace_recursive($storeData, $fieldWithValue);
        }

        return $storeData;
    }
}