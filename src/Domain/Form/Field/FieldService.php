<?php

namespace KikCMS\Domain\Form\Field;

use KikCMS\Domain\DataTable\Config\DataTableConfigService;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\Form\Field\Types\DatatableField;
use ReflectionClass;

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
            $field = $this->getFieldObject($fieldArray['type']);

            $field->setKey($key);
            $field->setField($fieldArray['field'] ?? $key);
            $field->setType($fieldArray['type']);

            if ($fieldArray['label'] ?? null) {
                $field->setLabel($fieldArray['label']);
            }

            $this->setTypeSpecificProperties($fieldArray, $field);

            $fields[$key] = $field;
        }

        return $fields;
    }

    private function getFieldObject(mixed $type): Field
    {
        $reflection = new ReflectionClass(Field::class);
        $namespace  = $reflection->getNamespaceName();

        $fieldTypeClass = $namespace . '\\Types\\' . ucfirst($type) . 'Field';

        if (class_exists($fieldTypeClass)) {
            return new $fieldTypeClass();
        }

        return new Field;
    }

    private function setTypeSpecificProperties(mixed $fieldArray, Field $field): void
    {
        if ($field instanceof DataTableField) {
            $field->setInstance($fieldArray['instance']);
        }
    }
}