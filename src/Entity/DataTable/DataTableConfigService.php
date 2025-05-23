<?php

namespace App\Entity\DataTable;

use App\Entity\App\CallableService;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class DataTableConfigService
{
    /** @var Parser */
    private Parser $yamlParser;

    /** @var ParameterBagInterface */
    private ParameterBagInterface $params;

    /** @var TranslatorInterface */
    private TranslatorInterface $translator;

    /** @var CallableService */
    private CallableService $callableService;

    /**
     * @param Parser $yamlParser
     * @param ParameterBagInterface $params
     * @param TranslatorInterface $translator
     * @param CallableService $callableService
     */
    public function __construct(Parser $yamlParser, ParameterBagInterface $params, TranslatorInterface $translator,
        CallableService $callableService)
    {
        $this->yamlParser      = $yamlParser;
        $this->params          = $params;
        $this->translator      = $translator;
        $this->callableService = $callableService;
    }

    /**
     * @param string $instance
     * @return DataTable
     */
    public function getFromConfigByInstance(string $instance): DataTable
    {
        $configPath = $this->params->get('kernel.project_dir') . '/config/datatables.yaml';

        if ( ! $dataTableConfig = $this->yamlParser->parseFile($configPath, Yaml::PARSE_CUSTOM_TAGS)[$instance] ?? null) {
            throw new Exception("No config found for DataTable '$instance'");
        }

        $form             = $dataTableConfig['form'] ?? [];
        $source           = $dataTableConfig['source'];
        $headers          = $dataTableConfig['headers'] ?? [];
        $headersTranslate = $dataTableConfig['headersTranslate'] ?? [];
        $buttons          = $dataTableConfig['buttons'] ?? [];
        $mobileColumns    = $dataTableConfig['mobileColumns'] ?? [];

        $sourceType = $source['type'] ?? SourceType::Pdo;
        $pdoModel   = $source['model'] ?? null;

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

        $dataTable = new DataTable;
        $dataTable->setInstance($instance);
        $dataTable->setSource($sourceType);
        $dataTable->setPdoModel($pdoModel);
        $dataTable->setHeaders($headers);
        $dataTable->setButtons($buttons);
        $dataTable->setMobileColumns($mobileColumns);
        $dataTable->setForm($form);

        return $dataTable;
    }

    /**
     * @param array $form
     * @return array
     */
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

    /**
     * @param array $fields
     * @return array
     */
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