<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Config\DataTableConfigService;
use KikCMS\Domain\DataTable\Config\DataTablePathService;
use KikCMS\Domain\DataTable\Config\SourceType;
use KikCMS\Domain\DataTable\Context\FormContext;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Form\DataTableFormService;
use KikCMS\Domain\DataTable\Rearrange\RearrangeService;
use KikCMS\Domain\Form\Field\Field;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\Form\Field\Types\DatatableField;

readonly class DataTableStoreService
{
    public function __construct(
        private FieldService $fieldService,
        private DataTablePathService $dataTablePathService,
        private DataTableConfigService $dataTableConfigService,
        private RearrangeService $rearrangeService,
        private DataTableFormService $dataTableFormService,
    ) {}

    public function getDataArrayToStore(DataTable $dataTable, DataTableFilters $filters, array $rawData): array
    {
        $storeData = [];

        $form   = $this->dataTableFormService->getForm($dataTable, new FormContext($rawData));
        $fields = $this->fieldService->getObjectMapByForm($form);

        foreach ($fields as $key => $field) {
            if ( ! array_key_exists($key, $rawData) || ! $this->fieldRequiresSave($field, $filters)) {
                continue;
            }

            $value    = $this->updateValueByFieldType($rawData[$key], $field, $filters);
            $field    = $field->getField();
            $langCode = $filters->getLangCode();

            $fieldWithValue = $this->dataTablePathService->convertPathToArray($field, $value, $langCode);

            $storeData = array_replace_recursive($storeData, $fieldWithValue);
        }

        if ($dataTable->isRearrange() && $dataTable->getSource() === SourceType::Pdo) {
            $maxDisplayOrder = $this->rearrangeService->getMaxDisplayOrder($dataTable->getPdoModel());

            $storeData[DataTableConfig::DISPLAY_ORDER] = $maxDisplayOrder + 1;
        }

        return $storeData;
    }

    private function fieldRequiresSave(Field $field, DataTableFilters $filters): bool
    {
        if ($field instanceof DatatableField) {
            $dataTable = $this->dataTableConfigService->getFromConfigByInstance($field->getInstance());

            if ($dataTable->getSource() === SourceType::Local) {
                return true;
            }

            return ! $filters->getParentId();
        }

        return true;
    }

    private function updateValueByFieldType(mixed $value, Field $field, DataTableFilters $filters): mixed
    {
        if ($field instanceof DatatableField) {
            $dataTable = $this->dataTableConfigService->getFromConfigByInstance($field->getInstance());

            foreach ($value as $index => $row) {
                $value[$index] = $this->getDataArrayToStore($dataTable, $filters, $row);
            }
        }

        return $value;
    }
}