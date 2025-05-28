<?php

namespace App\Domain\DataTable;

use Exception;
use Symfony\Polyfill\Intl\Icu\Exception\NotImplementedException;

class DataTableService
{
    /** @var DataTableConfigService */
    private DataTableConfigService $configService;

    /** @var DataTablePdoService */
    private DataTablePdoService $dataTablePdoService;

    /** @var DataTableLanguageResolver */
    private DataTableLanguageResolver $languageResolver;

    /**
     * @param DataTableConfigService $configService
     * @param DataTablePdoService $dataTablePdoService
     * @param DataTableLanguageResolver $languageResolver
     */
    public function __construct(DataTableConfigService $configService, DataTablePdoService $dataTablePdoService,
        DataTableLanguageResolver $languageResolver)
    {
        $this->configService       = $configService;
        $this->dataTablePdoService = $dataTablePdoService;
        $this->languageResolver    = $languageResolver;
    }

    /**
     * @param string $instance
     * @return array[]
     */
    public function getData(string $instance): array
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            return $this->dataTablePdoService->getData($dataTable);
        }

        return [];
    }

    /**
     * @param string $instance
     * @return array
     */
    public function getHeaders(string $instance): array
    {
        return $this->getByInstance($instance)->getHeaders();
    }

    /**
     * @param string $instance
     * @param string|null $langCode
     * @return DataTable
     * @throws Exception
     */
    public function getByInstance(string $instance, string $langCode = null): DataTable
    {
        $dataTable    = $this->configService->getFromConfigByInstance($instance);
        $resolvedLang = $this->languageResolver->resolve($langCode);

        $dataTable->setLangCode($resolvedLang);

        return $dataTable;
    }

    /**
     * @param string $instance
     * @param string $id
     * @return array|null
     */
    public function getEditData(string $instance, string $id): ?array
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            return $this->dataTablePdoService->getEditData($dataTable, $id);
        }

        throw new NotImplementedException('Not implemented yet');
    }

    /**
     * @param string $instance
     * @param string $id
     * @param array $data
     * @return void
     */
    public function update(string $instance, string $id, array $data): void
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            $this->dataTablePdoService->update($dataTable, $id, $data);
            return;
        }

        throw new NotImplementedException('Not implemented yet');
    }

    /**
     * @param string $instance
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function create(string $instance, array $data): void
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            $this->dataTablePdoService->create($dataTable, $data);
            return;
        }

        throw new NotImplementedException('Not implemented yet');
    }

    /**
     * @param string $instance
     * @return array
     */
    public function getFullConfig(string $instance): array
    {
        $dataTable = $this->getByInstance($instance);

        return [
            'buttons'       => $dataTable->getButtons(),
            'mobileColumns' => $dataTable->getMobileColumns(),
            'headers'       => $dataTable->getHeaders(),
            'data'          => $this->getData($instance),
            'instance'      => $instance,
        ];
    }
}