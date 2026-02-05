<?php

namespace KikCMS\Domain\Form\Field;

use KikCMS\Domain\DataTable\Config\DataTableConfigService;
use KikCMS\Domain\DataTable\DataTable;

readonly class FieldService
{
    public function __construct(
        private DataTableConfigService $dataTableConfigService
    ) {}

    /**
     * @return Field[]
     */
    public function getFieldMap(DataTable $dataTable, ?string $filterType = null): array
    {
        $fieldsArrayData = $this->dataTableConfigService->getFields($dataTable, $filterType);

        $fields = [];

        foreach ($fieldsArrayData as $key => $fieldArray) {
            $field = new Field();

            $field->setKey($key);
            $field->setField($fieldArray['field'] ?? $key);
            $field->setType($fieldArray['type']);

            if ($fieldArray['label'] ?? null) {
                $field->setLabel($fieldArray['label']);
            }

            $fields[$key] = $field;
        }

        return $fields;
    }
}