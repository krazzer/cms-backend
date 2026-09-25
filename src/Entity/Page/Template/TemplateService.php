<?php

namespace KikCMS\Entity\Page\Template;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\App\Path\PathConfig;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TemplateService
{
    public function __construct(
        private ConfigService $configService,
        private TranslatorInterface $translator,
    ) {}

    public function getConfig(): array
    {
        return $this->configService->getMerged(PathConfig::SUBDIR_THEME . '/templates', false, ['templates', 'fields']);
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

    public function getNameMap(): array
    {
        $templatesConfig = $this->getTemplatesConfig();

        $nameMap = [];

        foreach ($templatesConfig as $key => $template) {
            if (isset($template['label_trans'])) {
                $name = $this->translator->trans($template['label_trans']);
            } else {
                $name = $template['label'];
            }

            $nameMap[$key] = $name;
        }

        return $nameMap;
    }
}