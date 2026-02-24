<?php

namespace KikCMS\Domain\Form;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\Form\Field\FieldService;

readonly class FormService
{
    public function __construct(
        private FieldService $fieldService,
        private DataTableService $dataTableService
    ) {}

    public function getHelperData(array $form): array
    {
        $subData = [];

        $fieldMap = $this->fieldService->getByForm($form, DataTableConfig::FIELD_TYPE_DATATABLE);

        foreach ($fieldMap as $key => $field) {
            $subData[$key] = $this->dataTableService->getSubDataTableFieldHelperData($field);
        }

        return $subData;
    }
}