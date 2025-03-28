<?php

namespace App\Entity\DataTable;

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

    /**
     * @param Parser $yamlParser
     * @param ParameterBagInterface $params
     * @param TranslatorInterface $translator
     */
    public function __construct(Parser $yamlParser, ParameterBagInterface $params, TranslatorInterface $translator)
    {
        $this->yamlParser = $yamlParser;
        $this->params     = $params;
        $this->translator = $translator;
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

        $dataTable = new DataTable;
        $dataTable->setSource($sourceType);
        $dataTable->setPdoModel($pdoModel);
        $dataTable->setHeaders($headers);
        $dataTable->setForm($form);

        return $dataTable;
    }
}