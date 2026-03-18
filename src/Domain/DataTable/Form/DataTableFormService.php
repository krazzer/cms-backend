<?php

namespace KikCMS\Domain\DataTable\Form;

use KikCMS\Domain\App\Config\Provider\ConfigProviderRegistry;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\Form\Config\FormConfigService;
use KikCMS\Domain\Form\Form;

readonly class DataTableFormService
{
    public function __construct(
        private ConfigProviderRegistry $providerRegistry,
        private FormConfigService $formConfigService,
    ) {}

    public function getForm(DataTable $dataTable, ?Context $context = null): Form
    {
        return $this->formConfigService->getByConfig($this->getFormConfig($dataTable, $context), context: $context);
    }

    public function getFormConfig(DataTable $dataTable, ?Context $context = null): array
    {
        if ($formProvider = $dataTable->getFormProvider()) {
            return $this->providerRegistry->getConfig($formProvider, $context);
        }

        return $dataTable->getForm();
    }
}