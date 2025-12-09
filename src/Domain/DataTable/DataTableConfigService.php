<?php

namespace App\Domain\DataTable;

use App\Domain\App\CallableService;
use Exception;
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

        $dataTable = match ($class) {
            "pages" => new PagesDataTable,
            default => new DataTable,
        };

        $dataTable->setInstance($instance);
        $dataTable->setSource($sourceType);
        $dataTable->setPdoModel($pdoModel);
        $dataTable->setHeaders($headers);
        $dataTable->setButtons($buttons);
        $dataTable->setMobileColumns($mobileColumns);
        $dataTable->setForm($form);
        $dataTable->setCells($cells);
        $dataTable->setQuery($query);
        $dataTable->setModify($modify);

        return $dataTable;
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

    public function getDataByPath(array $data, string $path, string $locale): ?string
    {
        $keys  = $this->pathToKeys($path, $locale);
        $value = $data;

        foreach ($keys as $key) {
            if ( ! is_array($value) || ! array_key_exists($key, $value)) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }

    public function convertPathToArray(string $path, mixed $value, string $locale): array
    {
        $keys = $this->pathToKeys($path, $locale);

        $result = [];
        $ref    =& $result;

        foreach ($keys as $key) {
            $ref =& $ref[$key];
        }

        $ref = $value;

        return $result;
    }

    public function pathToKeys(string $path, string $locale): array
    {
        return explode('.', str_replace('*', $locale, $path));
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