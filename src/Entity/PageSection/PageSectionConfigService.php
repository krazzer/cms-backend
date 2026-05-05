<?php

namespace KikCMS\Entity\PageSection;

use Exception;
use KikCMS\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class PageSectionConfigService
{
    public function __construct(
        private KernelInterface $kernel,
        private Parser $yamlParser,
        private TranslatorInterface $translator,
    ) {}

    public function getSectionsConfig(): array
    {
        $name = 'sections';

        $filePath = $this->kernel->getCmsDir(Kernel::DIR_CONFIG_THEME . DIRECTORY_SEPARATOR . $name . '.yaml');

        if ($config = $this->yamlParser->parseFile($filePath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            return $config;
        }

        throw new Exception("No config found for Form '$name'");
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

        return $returnFields;
    }

    private function getLabel(array $section): string
    {
        return $section['label'] ?? $this->translator->trans($section['label_trans']);
    }

}