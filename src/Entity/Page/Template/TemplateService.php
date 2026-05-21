<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\Form\Config\FormConfigService;

readonly class TemplateService
{
    public function __construct(private FormConfigService $formConfigService) {}

    public function getConfig(): array
    {
        return $this->formConfigService->getConfigFromFile('templates');
    }

    public function getTemplateConfig(string $template): array
    {
        return $this->getTemplatesConfig()[$template];
    }

    public function getTemplatesConfig(): array
    {
        return $this->getConfig()['templates'];
    }

    public function getFieldsConfig(): array
    {
        return $this->getConfig()['fields'];
    }

    public function getFieldsByTemplate(string $template): array
    {
        $fields       = $this->getTemplateConfig($template)['fields'] ?? [];
        $fieldsConfig = $this->getFieldsConfig();

        return array_intersect_key($fieldsConfig, array_flip($fields));
    }

    public function getMap(): array
    {
        $templatesConfig = $this->getTemplatesConfig();

        return array_map(fn($template) => $template['name'], $templatesConfig);
    }
}