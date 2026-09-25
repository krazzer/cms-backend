<?php

namespace KikCMS\Entity\PageSection;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\App\Path\PathConfig;
use KikCMS\Entity\Page\Field\FieldService;

readonly class PageSectionConfigService
{
    public function __construct(
        private ConfigService $configService,
        private FieldService $fieldService,
    ) {}

    public function getConfig(): array
    {
        return $this->configService->getMerged(PathConfig::SUBDIR_THEME . '/sections');
    }

    public function getFieldsByType(string $type): array
    {
        $fields = $this->getConfig()[$type]['fields'] ?? [];

        return $this->fieldService->getFilteredConfig($fields);
    }

    public function getNameMap(): array
    {
        return array_map(fn($template) => $template['label'], $this->getConfig());
    }
}