<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\App\Config\Provider\ConfigProviderRegistry;
use KikCMS\Domain\Form\Config\FormConfigService;
use KikCMS\Domain\Form\Form;

readonly class DataTableFormService
{
    public function __construct(
        private ConfigProviderRegistry $providerRegistry,
        private FormConfigService $formConfigService,
    ) {}

    public function getForm(DataTable $dataTable): Form
    {
        return $this->formConfigService->getByConfig($this->getFormConfig($dataTable));
    }

    public function getFormConfig(DataTable $dataTable): array
    {
        if ($formProvider = $dataTable->getFormProvider()) {
            dlog($formProvider);
            return $this->providerRegistry->getConfig($formProvider);
        }

        return $dataTable->getForm();
    }
}