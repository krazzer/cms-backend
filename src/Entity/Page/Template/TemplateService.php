<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\App\Path\PathConfig;
use KikCMS\Entity\Page\Field\FieldService;

readonly class TemplateService
{
    public function __construct(
        private ConfigService $configService,
        private FieldService $fieldService,
    ) {}

    public function getConfig(): array
    {
        return $this->configService->getMerged(PathConfig::SUBDIR_THEME . '/templates');
    }

    public function getFieldsByTemplate(string $template): array
    {
        $fields = $this->getConfig()[$template]['fields'] ?? [];

        return $this->fieldService->getFilteredConfig($fields);
    }

    public function getNameMap(): array
    {
        return array_map(fn($template) => $template['label'], $this->getConfig());
    }
}