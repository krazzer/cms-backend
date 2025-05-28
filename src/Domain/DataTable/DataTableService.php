<?php

namespace App\Domain\DataTable;

use Symfony\Polyfill\Intl\Icu\Exception\NotImplementedException;

readonly class DataTableService
{
    public function __construct(
        private DataTableConfigService $configService,
        private DataTablePdoService $dataTablePdoService,
        private DataTableLanguageResolver $languageResolver
    ) {}

    public function getData(string $instance): array
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            return $this->dataTablePdoService->getData($dataTable);
        }

        return [];
    }

    public function getHeaders(string $instance): array
    {
        return $this->getByInstance($instance)->getHeaders();
    }

    public function getByInstance(string $instance, string $langCode = null): DataTable
    {
        $dataTable    = $this->configService->getFromConfigByInstance($instance);
        $resolvedLang = $this->languageResolver->resolve($langCode);

        $dataTable->setLangCode($resolvedLang);

        return $dataTable;
    }

    public function getEditData(string $instance, string $id): ?array
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            return $this->dataTablePdoService->getEditData($dataTable, $id);
        }

        throw new NotImplementedException('Not implemented yet');
    }

    public function update(string $instance, string $id, array $data): void
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            $this->dataTablePdoService->update($dataTable, $id, $data);
            return;
        }

        throw new NotImplementedException('Not implemented yet');
    }

    public function create(string $instance, array $data): void
    {
        $dataTable = $this->getByInstance($instance);

        if ($dataTable->getSource() == SourceType::Pdo) {
            $this->dataTablePdoService->create($dataTable, $data);
            return;
        }

        throw new NotImplementedException('Not implemented yet');
    }

    public function getFullConfig(string $instance): array
    {
        $dataTable = $this->getByInstance($instance);

        return [
            'buttons'       => $dataTable->getButtons(),
            'mobileColumns' => $dataTable->getMobileColumns(),
            'headers'       => $dataTable->getHeaders(),
            'cells'         => $dataTable->getCells(),
            'class'         => $dataTable->getClass(),
            'data'          => $this->getData($instance),
            'instance'      => $instance,
        ];
    }
}
