<?php

namespace KikCMS\Domain\Form\Config;

use Exception;
use KikCMS\Domain\App\Config\Provider\ConfigProviderRegistry;
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

    public function getObjectByName(string $name): Form
    {
        $filePath = $this->kernel->getCmsDir(Kernel::DIR_CONFIG_FORMS . DIRECTORY_SEPARATOR . $name . '.yaml');

        if ( ! $config = $this->yamlParser->parseFile($filePath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            throw new Exception("No config found for Form '$name'");
        }

        return $this->getByConfig($config, $name);
    }

    public function getByConfig(array $config, ?string $name = null): Form
    {
        $sourceType = $config['source']['type'] ?? '';
        $sourceType = SourceType::tryFrom($sourceType) ?? SourceType::KeyValue;

        $fields = $this->resolveFields($config);
        $tabs   = $config[DataTableConfig::FORM_TABS] ?? [];

        foreach ($tabs as &$tab) {
            $tab[FormConfig::FIELDS] = $this->resolveFields($tab);
        }

        $form = new Form()
            ->setTabs($tabs)
            ->setFields($fields)
            ->setSource($sourceType)
            ->setName($name);

        $this->resolveReferences($form);

        return $form;
    }

    public function resolveReferences(Form $form): void
    {
        $this->fieldService->walk($form, function ($field): array {
            if ($field[DataTableConfig::FIELD_TYPE] === DataTableConfig::FIELD_TYPE_SELECT) {
                return $this->resolveSelectFieldItems($field);
            }

            return $field;
        });
    }

    public function resolveSelectFieldItems(array $field): array
    {
        if ($itemProviderKey = $field[FieldConfig::ITEM_PROVIDER] ?? null) {
            $field[FieldConfig::ITEMS] = $this->providerRegistry->getConfig($itemProviderKey);
        }

        return $field;
    }

    private function resolveFields(array $config): array
    {
        if ($fieldProviderKey = $config[FormConfig::FIELD_PROVIDER] ?? null) {
            return $this->providerRegistry->getConfig($fieldProviderKey);
        }

        return $config[FormConfig::FIELDS] ?? [];
    }
}