<?php

namespace KikCMS\Domain\DataTable\Field;

use KikCMS\Domain\DataTable\DataTable;

class FieldService
{
    /**
     * @return Field[]
     */
    public function getFieldMap(DataTable $dataTable): array
    {
        $fieldsArrayData = $dataTable->getFormFields();
        $fields = [];

        foreach ($fieldsArrayData as $key => $fieldArray) {
            $field = new Field();

            $field->setKey($key);
            $field->setField($fieldArray['field'] ?? $key);
            $field->setType($fieldArray['type']);

            if($fieldArray['label'] ?? null){
                $field->setLabel($fieldArray['label']);
            }

            $fields[$key] = $field;
        }

        return $fields;
    }
}