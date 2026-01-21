<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\App\Exception\NotImplementedException;
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

        foreach ($dataTable->getFormFields($type) as $key => $field) {
            if ($default = $field['default'] ?? null) {
                $defaultData[$key] = $default;
            }
        }

        return $defaultData;
    }

    public function getEditData(DataTable $dataTable, string $id, StoreData $storeData): ?array
    {
        return $this->source($dataTable)->getEditData($dataTable, $id, $storeData);
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

    private function source(DataTable $dataTable): DataTableSourceServiceInterface
    {
        return $this->resolver->resolve($dataTable->getSource());
    }
}
