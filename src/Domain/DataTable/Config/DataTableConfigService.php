<?php

namespace KikCMS\Domain\DataTable\Config;

use Exception;
use KikCMS\Domain\App\Service\CallableService;
use KikCMS\Domain\DataTable\DataTable;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class DataTableConfigService
{
    public function __construct(
        private Parser $yamlParser,
        private ParameterBagInterface $params,
        private TranslatorInterface $translator,
        private CallableService $callableService
    ) {}

    public function getFields(DataTable $dataTable, ?string $filterType = null): array
    {
        return $this->getFieldsByForm($dataTable->getForm(), $filterType);
    }

    public function getFieldsByForm(array $form, ?string $filterType = null): array
    {
        $fields = [];

        $this->walkFields($form, function ($field, $key) use (&$fields, $filterType) {
            if ($filterType && $field[DataTableConfig::FIELD_TYPE] !== $filterType) {
                return;
            }

            $fields[$key] = $field;
        });

        return $fields;
    }

    public function getFromConfigByInstance(string $instance): DataTable
    {
        $configPath = $this->params->get('kernel.project_dir') . '/config/datatables/' . $instance . '.yaml';

        if ( ! $config = $this->yamlParser->parseFile($configPath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            throw new Exception("No config found for DataTable '$instance'");
        }

        $form             = $config['form'] ?? [];
        $actions          = $config['actions'] ?? [];
        $source           = $config['source'];
        $headers          = $config['headers'] ?? [];
        $headersTranslate = $config['headersTranslate'] ?? [];
        $buttons          = $config['buttons'] ?? [];
        $mobileColumns    = $config['mobileColumns'] ?? [];
        $cells            = $config['cells'] ?? [];
        $class            = $config['class'] ?? null;
        $searchColumns    = $config['searchColumns'] ?? [];
        $typeForms        = $config['typeForms'] ?? [];
        $rearrange        = $config['rearrange'] ?? false;

        $sourceType = SourceType::tryFrom($source['type'] ?? null) ?? SourceType::Pdo;

        $pdoModel = $source['model'] ?? null;
        $query    = $source['query'] ?? null;
        $modify   = $source['modify'] ?? null;

        if ($sourceType == SourceType::Pdo && ! $pdoModel) {
            throw new Exception("No Pdo model configured for DataTable '$instance'");
        }

        if ($sourceType == SourceType::Pdo && ! class_exists($pdoModel)) {
            throw new Exception("Class '$pdoModel' doesn't exist for DataTable '$instance'");
        }

        if ($headersTranslate) {
            $headers = array_map(fn($value) => $this->translator->trans($value), $headersTranslate);
        }

        $form = $this->updateFormConfig($form);

        $dataTable = (new DataTable)
            ->setInstance($instance)
            ->setSource($sourceType)
            ->setActions($actions)
            ->setHeaders($headers)
            ->setButtons($buttons)
            ->setMobileColumns($mobileColumns)
            ->setForm($form)
            ->setCells($cells)
            ->setQuery($query)
            ->setModify($modify)
            ->setSearchColumns($searchColumns)
            ->setTypeForms($typeForms)
            ->setRearrange($rearrange);

        if ($sourceType == SourceType::Pdo) {
            $dataTable->setPdoModel($pdoModel);
        }

        if ($class) {
            $dataTable->setClass($class);
        }

        return $dataTable;
    }

    public function updateFormConfig(array $form): array
    {
        return $this->walkFields($form, function ($field): array {
            if ($field[DataTableConfig::FIELD_TYPE] === DataTableConfig::FIELD_TYPE_SELECT) {
                return $this->resolveSelectFieldItems($field);
            }

            return $field;
        });
    }

    public function walkFields(array $node, callable $callback): array
    {
        foreach ($node[DataTableConfig::FORM_FIELDS] ?? [] as $i => $field) {
            $node[DataTableConfig::FORM_FIELDS][$i] = $callback($field, $i);
        }

        foreach ($node[DataTableConfig::FORM_TABS] ?? [] as $i => $tab) {
            $node[DataTableConfig::FORM_TABS][$i] = $this->walkFields($tab, $callback);
        }

        return $node;
    }

    public function resolveSelectFieldItems(array $field): array
    {
        $items = $field[DataTableConfig::FIELD_ITEMS] ?? [];

        if (empty($items)) {
            return $field;
        }

        if ( ! $callable = $this->callableService->getCallableByString($items)) {
            return $field;
        }

        $field[DataTableConfig::FIELD_ITEMS] = call_user_func($callable);

        return $field;
    }
}