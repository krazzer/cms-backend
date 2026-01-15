<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\App\Exception\NotImplementedException;
use KikCMS\Domain\App\Exception\ObjectNotFoundException;
use KikCMS\Domain\DataTable\Config\DataTableConfigService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;

readonly class DataTableService
{
    public function __construct(
        private DataTableConfigService $configService,
        private DataTablePdoService $dataTablePdoService,
        private DataTableLanguageResolver $languageResolver,
        private DataTableRowService $rowService, private DataTableLocalService $dataTableLocalService,
    ) {}

    public function getData(DataTable $dataTable, ?Filters $filters = null, ?StoreData $storeData = null): array
    {
        if ($dataTable->getSource() == SourceType::Pdo) {
            return $this->dataTablePdoService->getData($dataTable, $filters);
        }

        if ($dataTable->getSource() == SourceType::Local) {
            $viewData = [];

            if( ! $storeData){
                return $viewData;
            }

            foreach ($storeData->getData() as $id => $row) {
                $viewDataRow = $this->rowService->getRowView($row + ['id' => $id], $dataTable, $filters, $id);
                $viewData[]  = $viewDataRow->toArray();
            }

            return $viewData;
        }

        throw new NotImplementedException;
    }

    public function getHeaders(string $instance): array
    {
        return $this->getByInstance($instance)->getHeaders();
    }

    public function getByInstance(string $instance, ?string $langCode = null): DataTable
    {
        $dataTable    = $this->configService->getFromConfigByInstance($instance);
        $resolvedLang = $this->languageResolver->resolve($langCode);

        $dataTable->setLangCode($resolvedLang);

        return $dataTable;
    }

    public function getDefaultData(DataTable $dataTable, ?string $type = null): ?array
    {
        $defaultData = [];

        foreach ($dataTable->getFormFields($type) as $key => $field) {
            if ($default = $field['default'] ?? null) {
                $defaultData[$key] = $default;
            }
        }

        return $defaultData;
    }

    public function getEditData(DataTable $dataTable, string $id, StoreData $storeData): ?array
    {
        if ($dataTable->getSource() == SourceType::Pdo) {
            $editData = $this->dataTablePdoService->getEditData($dataTable, $id);

            if ( ! $editData) {
                throw new ObjectNotFoundException;
            }

            return $editData;
        }

        if ($dataTable->getSource() == SourceType::Local) {
            return $storeData->getData()[$id];
        }

        throw new NotImplementedException;
    }

    public function save(DataTable $dataTable, array $updateData, DataTableStoreData $storeData, ?string $id = null): int
    {
        if ($id) {
            $this->update($dataTable, $id, $updateData, $storeData);
        } else {
            $id = $this->create($dataTable, $updateData, $storeData);
        }

        return $id;
    }

    public function update(DataTable $dataTable, string $id, array $updateData, DataTableStoreData $storeData): void
    {
        if ($dataTable->getSource() == SourceType::Pdo) {
            $this->dataTablePdoService->update($dataTable, $id, $updateData);
            return;
        }

        if( $dataTable->getSource() == SourceType::Local){
            $this->dataTableLocalService->update($id, $updateData, $storeData);
        }
    }

    public function create(DataTable $dataTable, array $data, DataTableStoreData $storeData): int
    {
        if ($dataTable->getSource() == SourceType::Pdo) {
            return $this->dataTablePdoService->create($dataTable, $data);
        }

        if( $dataTable->getSource() == SourceType::Local){
            return $this->dataTableLocalService->create($data, $storeData);
        }

        throw new NotImplementedException;
    }

    public function delete(DataTable $dataTable, array $ids, StoreData $storeData): void
    {
        if ($dataTable->getSource() == SourceType::Pdo) {
            $this->dataTablePdoService->deleteList($dataTable, $ids);
        }

        if ($dataTable->getSource() == SourceType::Local) {
            $data = $storeData->getData();

            foreach ($ids as $id) {
                unset($data[$id]);
            }

            $storeData->setData($data);
        }
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
            'search'        => $dataTable->getSearch(),
            'source'        => $dataTable->getSource(),
            'data'          => $this->getData($dataTable),
            'instance'      => $instance,
        ];
    }

    public function updateCheckbox(DataTable $dataTable, int $id, string $field, bool $value): void
    {
        if ($dataTable->getSource() == SourceType::Pdo) {
            $this->dataTablePdoService->updateCheckbox($dataTable, $id, $field, $value);
            return;
        }

        throw new NotImplementedException;
    }

    public function getForm(DataTable $dataTable, ?string $type = null): array
    {
        return $dataTable->getForm($type);
    }
}
