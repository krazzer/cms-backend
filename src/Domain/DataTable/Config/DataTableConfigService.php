<?php

namespace KikCMS\Domain\DataTable\Config;

use Exception;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\Form\Config\FormConfigService;
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
        private FormConfigService $formConfigService,
    ) {}

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

        $sourceType = SourceType::tryFrom($source['type'] ?? '') ?? SourceType::Pdo;

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

        $form = $this->formConfigService->getByConfig($form);

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
}