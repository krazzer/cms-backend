<?php

namespace KikCMS\Domain\Form\Field;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\Form\Field\Types\DatatableField;
use ReflectionClass;

readonly class FieldService
{
    public function getByForm(array $form, ?string $filterType = null): array
    {
        $fields = [];

        $this->walk($form, function ($field, $key) use (&$fields, $filterType) {
            if ($filterType && $field[DataTableConfig::FIELD_TYPE] !== $filterType) {
                return;
            }

            $fields[$key] = $field;
        });

        return $fields;
    }

    /**
     * @return Field[]
     */
    public function getFieldMap(DataTable $dataTable, ?string $filterType = null): array
    {
        $fieldsArrayData = $this->getByForm($dataTable->getForm($filterType));

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

    public function walk(array $node, callable $callback): array
    {
        foreach ($node[DataTableConfig::FORM_FIELDS] ?? [] as $i => $field) {
            $node[DataTableConfig::FORM_FIELDS][$i] = $callback($field, $i);
        }

        foreach ($node[DataTableConfig::FORM_TABS] ?? [] as $i => $tab) {
            $node[DataTableConfig::FORM_TABS][$i] = $this->walk($tab, $callback);
        }

        return $node;
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