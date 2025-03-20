<?php

namespace App\Entity\DataTable;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Parser;

class DataTableConfigService
{
    /** @var Parser */
    private Parser $yamlParser;

    /** @var ParameterBagInterface */
    private ParameterBagInterface $params;

    /**
     * @param Parser $yamlParser
     * @param ParameterBagInterface $params
     */
    public function __construct(Parser $yamlParser, ParameterBagInterface $params)
    {
        $this->yamlParser = $yamlParser;
        $this->params     = $params;
    }

    /**
     * @param string $instance
     * @return DataTable
     */
    public function getFromConfigByInstance(string $instance): DataTable
    {
        $configPath = $this->params->get('kernel.project_dir') . '/config/datatables.yaml';

        if ( ! $dataTableConfig = $this->yamlParser->parseFile($configPath)[$instance] ?? null) {
            throw new Exception("No config found for DataTable '$instance'");
        }

        $source     = $dataTableConfig['source'];
        $sourceType = $source['type'] ?? SourceType::Pdo;
        $pdoModel   = $source['model'] ?? null;

        if ($sourceType == SourceType::Pdo && ! $pdoModel) {
            throw new Exception("No Pdo model configured for DataTable '$instance'");
        }

        if ($sourceType == SourceType::Pdo && ! class_exists($pdoModel)) {
            throw new Exception("Class '$pdoModel' doesn't exist for DataTable '$instance'");
        }

        $dataTable = new DataTable;
        $dataTable->setSource($sourceType);
        $dataTable->setPdoModel($pdoModel);

        return $dataTable;
    }
}