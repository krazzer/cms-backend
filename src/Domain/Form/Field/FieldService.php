<?php

namespace KikCMS\Domain\Form\Field;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\Form\Field\Types\DatatableField;
use KikCMS\Domain\Form\Form;
use ReflectionClass;

readonly class FieldService
{
    public function getByForm(Form $form, ?string $filterType = null): array
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

    public function getObjectMapByForm(Form $form): array
    {
        $fieldsArrayData = $this->getByForm($form);

        $fields = [];

        foreach ($fieldsArrayData as $key => $fieldArray) {
            $field = $this->getFieldObject($fieldArray['type']);

            $field->setKey($key);
            $field->setField($fieldArray['field'] ?? $key);
            $field->setType($fieldArray['type']);
            $field->setStore($fieldArray['store'] ?? true);

            if ($fieldArray['label'] ?? null) {
                $field->setLabel($fieldArray['label']);
            }

            $this->setTypeSpecificProperties($fieldArray, $field);

            $fields[$key] = $field;
        }

        return $fields;
    }

    /**
     * @return Field[]
     */
    public function getObjectMapByDataTable(DataTable $dataTable, ?string $filterType = null): array
    {
        return $this->getObjectMapByForm($dataTable->getForm($filterType));
    }

    public function walk(Form $form, callable $callback): void
    {
        foreach ($form->getFields() as $key => $field) {
            if($field = $callback($field, $key)) {
                $form->setField($key, $field);
            }
        }

        foreach ($form->getTabs() as $tabKey => $tab) {
            foreach ($tab[DataTableConfig::FORM_FIELDS] as $fieldKey => $field) {
                if($field = $callback($field, $fieldKey)) {
                    $form->setTabField($tabKey, $fieldKey, $field);
                }
            }
        }
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