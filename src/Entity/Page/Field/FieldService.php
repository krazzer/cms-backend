<?php

namespace KikCMS\Entity\Page\Field;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\App\Path\PathConfig;

readonly class FieldService
{
    public function __construct(
        private ConfigService $configService,
    ) {}

    public function getConfig(): array
    {
        return $this->configService->getMerged(PathConfig::SUBDIR_THEME . '/fields', true);
    }

    public function getFilteredConfig(array $fields): array
    {
        $fieldsConfig = $this->getConfig();

        $returnFields = [];

        foreach ($fields as $field) {
            if (isset($fieldsConfig[$field])) {
                $returnFields[$field] = $fieldsConfig[$field];
            }
        }

        return $returnFields;
    }
}