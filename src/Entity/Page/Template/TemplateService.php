<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\App\Path\PathConfig;

readonly class TemplateService
{
    public function __construct(private ConfigService $configService) {}

    public function getConfig(): array
    {
        return $this->configService->getConfigFromFile(PathConfig::SUBDIR_THEME . '/templates');
    }

    public function getTemplateConfig(string $template): array
    {
        return $this->getTemplatesConfig()[$template] ?? [];
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

        $result = [];

        foreach ($fields as $field) {
            $result[$field] = $fieldsConfig[$field];
        }

        return $result;
    }

    public function getMap(): array
    {
        $templatesConfig = $this->getTemplatesConfig();

        return array_map(fn($template) => $template['name'], $templatesConfig);
    }
}