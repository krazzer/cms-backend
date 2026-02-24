<?php

namespace KikCMS\Domain\Form\Config;

use Exception;
use KikCMS\Domain\App\Service\CallableService;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
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
        private CallableService $callableService,
        private FieldService $fieldService
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
        $sourceType = $config['source']['type'] ?? null;
        $sourceType = SourceType::tryFrom($sourceType) ?? SourceType::KeyValue;

        $form = new Form();

        $form->setTabs($config['tabs'] ?? []);
        $form->setFields($config['fields'] ?? []);
        $form->setSource($sourceType);
        $form->setName($name);

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
        $items = $field[DataTableConfig::FIELD_ITEMS] ?? [];

        if (empty($items) || is_array($items)) {
            return $field;
        }

        if ( ! $callable = $this->callableService->getCallableByString($items)) {
            return $field;
        }

        $field[DataTableConfig::FIELD_ITEMS] = call_user_func($callable);

        return $field;
    }
}