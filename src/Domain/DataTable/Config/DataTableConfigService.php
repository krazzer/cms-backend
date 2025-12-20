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

        if ( ! $dataTableConfig = $this->yamlParser->parseFile($configPath, Yaml::PARSE_CUSTOM_TAGS) ?? null) {
            throw new Exception("No config found for DataTable '$instance'");
        }

        $form             = $dataTableConfig['form'] ?? [];
        $source           = $dataTableConfig['source'];
        $headers          = $dataTableConfig['headers'] ?? [];
        $headersTranslate = $dataTableConfig['headersTranslate'] ?? [];
        $buttons          = $dataTableConfig['buttons'] ?? [];
        $mobileColumns    = $dataTableConfig['mobileColumns'] ?? [];
        $cells            = $dataTableConfig['cells'] ?? [];
        $class            = $dataTableConfig['class'] ?? null;
        $searchColumns    = $dataTableConfig['searchColumns'] ?? [];
        $typeForms        = $dataTableConfig['typeForms'] ?? [];

        $sourceType = $source['type'] ?? SourceType::Pdo;
        $pdoModel   = $source['model'] ?? null;
        $query      = $source['query'] ?? null;
        $modify     = $source['modify'] ?? null;

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

        return (new DataTable)
            ->setInstance($instance)
            ->setSource($sourceType)
            ->setPdoModel($pdoModel)
            ->setHeaders($headers)
            ->setButtons($buttons)
            ->setMobileColumns($mobileColumns)
            ->setForm($form)
            ->setCells($cells)
            ->setQuery($query)
            ->setModify($modify)
            ->setSearchColumns($searchColumns)
            ->setClass($class)
            ->setTypeForms($typeForms);
    }

    public function updateFormConfig(array $form): array
    {
        if (isset($form['fields'])) {
            $form['fields'] = $this->resolveSelectFieldItems($form['fields']);
        }

        if (isset($form['tabs'])) {
            foreach ($form['tabs'] as &$tab) {
                if (isset($tab['fields'])) {
                    $tab['fields'] = $this->resolveSelectFieldItems($tab['fields']);
                }
            }
        }

        return $form;
    }

    private function resolveSelectFieldItems(array $fields): array
    {
        foreach ($fields as &$field) {
            if (($field['type'] ?? null) === DataTableConfig::FIELD_TYPE_SELECT && ! empty($field['items'])) {
                if ($callable = $this->callableService->getCallableByString($field['items'])) {
                    $field['items'] = call_user_func($callable);
                }
            }
        }

        return $fields;
    }
}