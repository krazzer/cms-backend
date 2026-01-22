<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\App\Exception\NotImplementedException;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Config\DataTableConfigService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;
use KikCMS\Domain\DataTable\SourceService\DataTableSourceServiceInterface;
use KikCMS\Domain\DataTable\SourceService\DataTableSourceServiceResolver;

readonly class DataTableService
{
    public function __construct(
        private DataTableConfigService $configService,
        private DataTableLanguageResolver $languageResolver,
        private DataTableSourceServiceResolver $resolver,
        private DataTableConfigService $dataTableConfigService,
    ) {}

    public function getData(DataTable $dataTable, ?Filters $filters = null, ?StoreData $storeData = null): array
    {
        return $this->source($dataTable)->getData($dataTable, $filters, $storeData);
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

        $fields = $this->dataTableConfigService->getFieldsByForm($dataTable->getForm($type));

        foreach ($fields as $key => $field) {
            if ($default = $field['default'] ?? null) {
                $defaultData[$key] = $default;
            }
        }

        return $defaultData;
    }

    public function getEditData(DataTable $dataTable, string $id, StoreData $storeData): array
    {
        return $this->source($dataTable)->getEditData($dataTable, $id, $storeData);
    }

    public function getPayloadByInstance(string $instance): array
    {
        $dataTable = $this->getByInstance($instance);

        return [
            DataTableConfig::HELPER_SETTINGS => $this->getFullConfig($dataTable),
            DataTableConfig::HELPER_DATA     => $this->getData($dataTable),
        ];
    }

    public function save(DataTable $dataTable, array $updateData, StoreData $storeData, ?string $id = null): int
    {
        if ($id) {
            $this->update($dataTable, $id, $updateData, $storeData);
        } else {
            $id = $this->create($dataTable, $updateData, $storeData);
        }

        return $id;
    }

    public function update(DataTable $dataTable, string $id, array $updateData, StoreData $storeData): void
    {
        $this->source($dataTable)->update($dataTable, $id, $updateData, $storeData);
    }

    public function create(DataTable $dataTable, array $data, StoreData $storeData): int
    {
        return $this->source($dataTable)->create($dataTable, $data, $storeData);
    }

    public function delete(DataTable $dataTable, array $ids, StoreData $storeData): void
    {
        $this->source($dataTable)->deleteList($dataTable, $ids, $storeData);
    }

    public function getFullConfig(DataTable $dataTable): array
    {
        return [
            'buttons'       => $dataTable->getButtons(),
            'mobileColumns' => $dataTable->getMobileColumns(),
            'headers'       => $dataTable->getHeaders(),
            'cells'         => $dataTable->getCells(),
            'class'         => $dataTable->getClass(),
            'search'        => $dataTable->getSearch(),
            'source'        => $dataTable->getSource(),
            'instance'      => $dataTable->getInstance(),
        ];
    }

    public function getFullConfigByInstance(string $instance): array
    {
        return $this->getFullConfig($this->getByInstance($instance));
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

    public function getSubDataTableHelperData(DataTable $dataTable, ?array $editData = null): array
    {
        $subData = [];

        $fieldMap = $this->dataTableConfigService->getFields($dataTable, DataTableConfig::FIELD_TYPE_DATATABLE);

        foreach ($fieldMap as $key => $field) {
            $subData[$key] = $this->getSubDataTableFieldHelperData($field, $editData[$key] ?? null);
        }

        return $subData;
    }

    public function getSubDataTableFieldHelperData(array $field, ?array $editData = null): array
    {
        $dataTable = $this->getByInstance($field[DataTableConfig::FIELD_INSTANCE]);

        $helperData = [DataTableConfig::HELPER_SETTINGS => $this->getFullConfig($dataTable)];

        if ($editData !== null) {
            $helperData[DataTableConfig::HELPER_DATA] = $this->getData($dataTable, null, new StoreData($editData));
        }

        return $helperData;
    }

    private function source(DataTable $dataTable): DataTableSourceServiceInterface
    {
        return $this->resolver->resolve($dataTable->getSource());
    }

}
