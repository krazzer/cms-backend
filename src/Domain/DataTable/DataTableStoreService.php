<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Config\DataTableConfigService;
use KikCMS\Domain\DataTable\Config\DataTablePathService;
use KikCMS\Domain\DataTable\Config\SourceType;
use KikCMS\Domain\DataTable\Rearrange\RearrangeService;
use KikCMS\Domain\Form\Field\Field;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\Form\Field\Types\DatatableField;

readonly class DataTableStoreService
{
    public function __construct(
        private FieldService $fieldService,
        private DataTablePathService $dataTablePathService,
        private DataTableConfigService $dataTableConfigService,
        private RearrangeService $rearrangeService,
    ) {}

    public function getDataArrayToStore(DataTable $dataTable, DataTableFilters $filters, array $rawData): array
    {
        $storeData = [];

        $fields = $this->fieldService->getObjectMapByDataTable($dataTable);

        foreach ($fields as $key => $field) {
            if ( ! array_key_exists($key, $rawData)) {
                continue;
            }

            $value = $this->updateValueByFieldType($rawData[$key], $field);
            $field = $field->getField();

            $fieldWithValue = $this->dataTablePathService->convertPathToArray($field, $value, $filters->getLangCode());

            $storeData = array_replace_recursive($storeData, $fieldWithValue);
        }

        if ($dataTable->isRearrange()) {
            $maxDisplayOrder = $this->rearrangeService->getMaxDisplayOrder($dataTable->getPdoModel());

            $storeData[DataTableConfig::DISPLAY_ORDER] = $maxDisplayOrder + 1;
        }

        return $storeData;
    }

    private function updateValueByFieldType(mixed $value, Field $field): mixed
    {
        if ($field instanceof DatatableField) {
            $dataTable = $this->dataTableConfigService->getFromConfigByInstance($field->getInstance());

            if ($dataTable->isRearrange() && $dataTable->getSource() === SourceType::Pdo) {
                $maxDisplayOrder = $this->rearrangeService->getMaxDisplayOrder($dataTable->getPdoModel());

                foreach ($value as $index => $row) {
                    $maxDisplayOrder++;
                    $value[$index][DataTableConfig::DISPLAY_ORDER] = $maxDisplayOrder;
                }
            }
        }

        return $value;
    }
}