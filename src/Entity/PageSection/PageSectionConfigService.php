<?php

namespace KikCMS\Entity\PageSection;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class PageSectionConfigService
{
    public function __construct(
        private TranslatorInterface $translator,
        private ConfigService $configService,
    ) {}

    public function getSectionsConfig(): array
    {
        return $this->configService->getMerged(PathConfig::SUBDIR_THEME . '/sections', false, ['sections', 'fields']);
    }

    public function getSectionNameMap(): array
    {
        return array_map([$this, 'getLabel'], $this->getSectionsConfig()['sections']);
    }

    public function getFieldsByType(string $type): array
    {
        $config = $this->getSectionsConfig();

        $allFields = $config['fields'];

        $fields = $config['sections'][$type]['fields'] ?? [];

        $returnFields = [];

        foreach ($fields as $field) {
            $returnFields[$field] = $allFields[$field];
        }

        foreach($returnFields as $key => $field) {
            $returnFields[$key]['label'] = $this->getLabel($field);
        }

        return $returnFields;
    }

    private function getLabel(array $data): string
    {
        return $data['label'] ?? $this->translator->trans($data['label_trans']);
    }

}