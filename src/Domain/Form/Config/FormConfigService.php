<?php

namespace KikCMS\Domain\Form\Config;

use KikCMS\Domain\App\Config\ConfigService;
use KikCMS\Domain\App\Config\Provider\ConfigProviderRegistry;
use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\App\Path\PathConfig;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\Form\Field\Config\FieldConfig;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\Form\Form;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class FormConfigService
{
    public function __construct(
        private FieldService $fieldService,
        private ConfigProviderRegistry $providerRegistry,
        private TranslatorInterface $translator,
        private ConfigService $configService,
    ) {}

    public function getConfigFromFile(string $name): array
    {
        return $this->configService->getConfigFromFile(PathConfig::SUBDIR_FORMS . '/' . $name);
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
        $this->translateFields($form);

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
        $fields           = $config[FormConfig::FIELDS] ?? [];
        $fieldProviderKey = $config[FormConfig::FIELD_PROVIDER] ?? null;
        $fieldProviderAdd = $config[FormConfig::FIELD_PROVIDER_ADD] ?? false;

        if ( ! $fieldProviderKey) {
            return $fields;
        }

        $providedFields = $this->providerRegistry->getConfig($fieldProviderKey, $context);

        return $fieldProviderAdd ? $fields + $providedFields : $providedFields;
    }

    private function translateFields(Form $form): void
    {
        $this->fieldService->walk($form, function ($field): array {
            if (array_key_exists(FieldConfig::LABEL_TRANS, $field)) {
                $field[FieldConfig::LABEL] = $this->translator->trans($field[FieldConfig::LABEL_TRANS]);
            }

            if (array_key_exists(FieldConfig::ITEMS_TRANS, $field)) {
                $items = $field[FieldConfig::ITEMS_TRANS];

                foreach ($items as $key => $item) {
                    $items[$key] = $this->translator->trans($item);
                }

                $field[FieldConfig::ITEMS] = $items;
            }

            return $field;
        });
    }
}