<?php

namespace KikCMS\Domain\Form\Config;

use Exception;
use KikCMS\Domain\App\Config\Provider\ConfigProviderRegistry;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\Form\Field\Config\FieldConfig;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\Form\Form;
use KikCMS\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

readonly class FormConfigService
{
    public function __construct(
        private KernelInterface $kernel,
        private Parser $yamlParser,
        private FieldService $fieldService,
        private ConfigProviderRegistry $providerRegistry
    ) {}

    public function getConfigFromFile(string $name): array
    {
        $filePath = $this->kernel->getCmsDir(Kernel::DIR_CONFIG_FORMS . DIRECTORY_SEPARATOR . $name . '.yaml');

        if ($config = $this->yamlParser->parseFile($filePath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            return $config;
        }

        throw new Exception("No config found for Form '$name'");
    }

    public function getObjectByName(string $name): Form
    {
        return $this->getByConfig($this->getConfigFromFile($name), $name);
    }

    public function getByConfig(array $config, ?string $name = null, ?Context $context = null): Form
    {
        $sourceType = $config['source']['type'] ?? '';
        $sourceType = SourceType::tryFrom($sourceType) ?? SourceType::KeyValue;

        $fields = $this->resolveFields($config, $context);
        $tabs   = $config[DataTableConfig::FORM_TABS] ?? [];

        foreach ($tabs as &$tab) {
            $tab[FormConfig::FIELDS] = $this->resolveFields($tab, $context);
        }

        $form = new Form()
            ->setTabs($tabs)
            ->setFields($fields)
            ->setSource($sourceType)
            ->setName($name);

        $this->resolveReferences($form, $context);

        return $form;
    }

    public function resolveReferences(Form $form, ?Context $context): void
    {
        $this->fieldService->walk($form, function ($field) use ($context): array {
            if ($field[DataTableConfig::FIELD_TYPE] === DataTableConfig::FIELD_TYPE_SELECT) {
                return $this->resolveSelectFieldItems($field, $context);
            }

            return $field;
        });
    }

    public function resolveSelectFieldItems(array $field, ?Context $context): array
    {
        if ($itemProviderKey = $field[FieldConfig::ITEM_PROVIDER] ?? null) {
            $field[FieldConfig::ITEMS] = $this->providerRegistry->getConfig($itemProviderKey, $context);
        }

        return $field;
    }

    private function resolveFields(array $config, ?Context $context): array
    {
        if ($fieldProviderKey = $config[FormConfig::FIELD_PROVIDER] ?? null) {
            return $this->providerRegistry->getConfig($fieldProviderKey, $context);
        }

        return $config[FormConfig::FIELDS] ?? [];
    }
}