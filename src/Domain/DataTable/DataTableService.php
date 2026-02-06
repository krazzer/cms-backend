<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Doctrine\Service\RelationService;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Config\DataTableConfigService;
use KikCMS\Domain\DataTable\Config\SourceType;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Filter\DataTablePdoFilterService;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Domain\DataTable\SourceService\DataTableSourceServiceInterface;
use KikCMS\Domain\DataTable\SourceService\DataTableSourceServiceResolver;

readonly class DataTableService
{
    public function __construct(
        private DataTableConfigService $configService,
        private DataTableSourceServiceResolver $resolver,
        private DataTableConfigService $dataTableConfigService,
        private DataTablePdoFilterService $dataTableFilterService,
        private RelationService $relationService,
    ) {}

    public function getData(DataTable $dataTable, Filters $filters, ?StoreData $storeData = null): array
    {
        return $this->source($dataTable, $filters)->getData($dataTable, $filters, $storeData);
    }

    public function getHeaders(string $instance): array
    {
        return $this->getByInstance($instance)->getHeaders();
    }

    public function getByInstance(string $instance): DataTable
    {
        return $this->configService->getFromConfigByInstance($instance);
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

    public function getEditData(DataTable $dataTable, Filters $filters, int $id, StoreData $storeData): array
    {
        return $this->source($dataTable, $filters)->getEditData($dataTable, $filters, $id, $storeData);
    }

    public function getPayloadByInstance(string $instance, ?Filters $filters = null): array
    {
        $dataTable = $this->getByInstance($instance);
        $filters   = $filters ?? $this->dataTableFilterService->getDefault();

        return [
            DataTableConfig::HELPER_SETTINGS => $this->getFullConfig($dataTable),
            DataTableConfig::HELPER_DATA     => $this->getData($dataTable, $filters),
        ];
    }

    public function save(DataTable $dataTable, Filters $filters, array $updateData, StoreData $storeData, ?string $id = null): int
    {
        if ($id) {
            $this->update($dataTable, $filters, $id, $updateData, $storeData);
        } else {
            $id = $this->create($dataTable, $filters, $updateData, $storeData);
        }

        return $id;
    }

    public function update(DataTable $dataTable, Filters $filters, string $id, array $updateData, StoreData $storeData): void
    {
        $this->source($dataTable, $filters)->update($dataTable, $filters, $id, $updateData, $storeData);
    }

    public function create(DataTable $dataTable, Filters $filters, array $data, StoreData $storeData): int
    {
        return $this->source($dataTable, $filters)->create($dataTable, $filters, $data, $storeData);
    }

    public function delete(DataTable $dataTable, Filters $filters, array $ids, StoreData $storeData): void
    {
        $this->source($dataTable, $filters)->deleteList($dataTable, $ids, $storeData);
    }

    public function getFullConfig(DataTable $dataTable): array
    {
        $actions = $dataTable->getActions();

        if ($dataTable->isRearrange()) {
            $actions[] = ['key' => 'rearrange', 'type' => 'rearrange'];
        }

        return [
            'buttons'       => $dataTable->getButtons(),
            'mobileColumns' => $dataTable->getMobileColumns(),
            'headers'       => $dataTable->getHeaders(),
            'cells'         => $dataTable->getCells(),
            'class'         => $dataTable->getClass(),
            'search'        => $dataTable->getSearch(),
            'source'        => $dataTable->getSource(),
            'instance'      => $dataTable->getInstance(),
            'actions'       => $actions,
        ];
    }

    public function getFullConfigByInstance(string $instance): array
    {
        return $this->getFullConfig($this->getByInstance($instance));
    }

    public function updateCheckbox(DataTable $dataTable, Filters $filters, int $id, string $field, bool $value, StoreData $storeData): void
    {
        $this->source($dataTable)->updateCheckbox($dataTable, $filters, $id, $field, $value, $storeData);
    }

    public function getForm(DataTable $dataTable, ?string $type = null): array
    {
        return $dataTable->getForm($type);
    }

    public function getSubDataTableHelperData(DataTable $dataTable, ?string $id = null, ?array $editData = null): array
    {
        $subData = [];

        $fieldMap = $this->dataTableConfigService->getFields($dataTable, DataTableConfig::FIELD_TYPE_DATATABLE);

        foreach ($fieldMap as $key => $field) {
            $subData[$key] = $this->getSubDataTableFieldHelperData($dataTable, $field, $id, $editData[$key] ?? []);
        }

        return $subData;
    }

    public function getSubDataTableFieldHelperData(DataTable $dataTable, array $field, ?string $id, array $editData): array
    {
        $subDataTable = $this->getByInstance($field[DataTableConfig::FIELD_INSTANCE]);

        $filters = $this->dataTableFilterService->getDefault()
            ->setParentDataTable($dataTable)
            ->setParentId($id);

        $helperData = [DataTableConfig::HELPER_SETTINGS => $this->getFullConfig($subDataTable)];

        $helperData[DataTableConfig::HELPER_DATA] = $this->getData($subDataTable, $filters, new StoreData($editData));

        return $helperData;
    }

    public function rearrange(DataTable $dataTable, Filters $filters, int $source, int $target, Location $location,
        StoreData $storeData): void
    {
        $this->source($dataTable, $filters)->rearrange($dataTable, $source, $target, $location, $storeData);
    }

    private function source(DataTable $dataTable, ?Filters $filters = null): DataTableSourceServiceInterface
    {
        $sourceType = $dataTable->getSource();

        if ($this->relationService->hasUnSavedParentDataTable($dataTable, $filters)) {
            $sourceType = SourceType::Local;
        }

        return $this->resolver->resolve($sourceType);
    }
}
