<?php

namespace KikCMS\Domain\DataTable\Config;

use Exception;
use KikCMS\Domain\App\CallableService;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\SourceType;
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

    public function getFromConfigByInstance(string $instance): DataTable
    {
        $configPath = $this->params->get('kernel.project_dir') . '/config/datatables/' . $instance . '.yaml';

        if ( ! $config = $this->yamlParser->parseFile($configPath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            throw new Exception("No config found for DataTable '$instance'");
        }

        $form             = $config['form'] ?? [];
        $source           = $config['source'];
        $headers          = $config['headers'] ?? [];
        $headersTranslate = $config['headersTranslate'] ?? [];
        $buttons          = $config['buttons'] ?? [];
        $mobileColumns    = $config['mobileColumns'] ?? [];
        $cells            = $config['cells'] ?? [];
        $class            = $config['class'] ?? null;
        $searchColumns    = $config['searchColumns'] ?? [];
        $typeForms        = $config['typeForms'] ?? [];

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
            ->setHeaders($headers)
            ->setButtons($buttons)
            ->setMobileColumns($mobileColumns)
            ->setForm($form)
            ->setCells($cells)
            ->setQuery($query)
            ->setModify($modify)
            ->setSearchColumns($searchColumns)
            ->setTypeForms($typeForms);

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
            if ($field[DataTableConfig::KEY_FIELD_TYPE] === DataTableConfig::FIELD_TYPE_SELECT) {
                return $this->resolveSelectFieldItems($field);
            }

            if ($field[DataTableConfig::KEY_FIELD_TYPE] === DataTableConfig::FIELD_TYPE_DATATABLE) {
                return $this->resolveDataTableSettings($field);
            }

            return $field;
        });
    }

    public function walkFields(array $node, callable $callback): array
    {
        foreach ($node[DataTableConfig::KEY_FORM_FIELDS] ?? [] as $i => $field) {
            $node[DataTableConfig::KEY_FORM_FIELDS][$i] = $callback($field);
        }

        foreach ($node[DataTableConfig::KEY_FORM_TABS] ?? [] as $i => $tab) {
            $node[DataTableConfig::KEY_FORM_TABS][$i] = $this->walkFields($tab, $callback);
        }

        return $node;
    }

    public function resolveSelectFieldItems(array $field): array
    {
        $items = $field[DataTableConfig::KEY_FIELD_ITEMS] ?? [];

        if (empty($items)) {
            return $field;
        }

        if ( ! $callable = $this->callableService->getCallableByString($items)) {
            return $field;
        }

        $field[DataTableConfig::KEY_FIELD_ITEMS] = call_user_func($callable);

        return $field;
    }

    public function resolveDataTableSettings($field): array
    {
        $instance = $field[DataTableConfig::KEY_FIELD_INSTANCE];

        $field[DataTableConfig::KEY_FIELD_SETTINGS] = $this->getFromConfigByInstance($instance);

        return $field;
    }
}