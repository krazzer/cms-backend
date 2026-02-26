<?php

namespace KikCMS\Domain\Form;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\Form\Config\FormConfigService;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\Form\Source\SourceService;

readonly class FormService
{
    public function __construct(
        private FieldService $fieldService,
        private DataTableService $dataTableService,
        private FormConfigService $formConfigService,
        private SourceService $sourceService
    ) {}

    public function getByName(string $name): Form
    {
        return $this->formConfigService->getObjectByName($name);
    }

    public function getHelperData(Form $form, array $data): array
    {
        $subData = [];

        $fieldMap = $this->fieldService->getByForm($form, DataTableConfig::FIELD_TYPE_DATATABLE);

        foreach ($fieldMap as $key => $field) {
            $subData[$key] = $this->dataTableService->getSubDataTableFieldHelperData($field, $data[$key] ?? []);
        }

        return $subData;
    }

    public function getPayloadByName(string $string): array
    {
        $form = $this->formConfigService->getObjectByName($string);
        $data = $this->sourceService->getData($form);

        return [
            'settings'   => $this->getFullConfig($form),
            'helperData' => $this->getHelperData($form, $data),
            'data'       => $data,
        ];
    }

    public function getFullConfig(Form $form): array
    {
        $config = [
            'fields' => $form->getFields(),
            'source' => $form->getSource(),
            'name'   => $form->getName(),
        ];

        if ($tabs = $form->getTabs()) {
            $config['tabs'] = $tabs;
        }

        return $config;
    }
}