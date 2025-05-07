<?php

namespace App\Entity\DataTable;

class DataTableService
{
    /** @var DataTableConfigService */
    private DataTableConfigService $configService;

    /** @var DataTablePdoService */
    private DataTablePdoService $dataTablePdoService;

    /**
     * @param DataTableConfigService $configService
     * @param DataTablePdoService $dataTablePdoService
     */
    public function __construct(DataTableConfigService $configService, DataTablePdoService $dataTablePdoService)
    {
        $this->configService       = $configService;
        $this->dataTablePdoService = $dataTablePdoService;
    }

    /**
     * @param string $instance
     * @return array[]
     */
    public function getData(string $instance): array
    {
        $dataTable = $this->configService->getFromConfigByInstance($instance);

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
        return $this->configService->getFromConfigByInstance($instance)->getHeaders();
    }

    /**
     * @param string $instance
     * @return DataTable
     */
    public function getByInstance(string $instance): DataTable
    {
        return $this->configService->getFromConfigByInstance($instance);
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

        // not implemented yet
        return [];
    }
}