<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Domain\DataTable\Config\SourceType;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Modifier\DataTableModifierService;
use KikCMS\Domain\DataTable\Modifier\ViewRowDataModifierInterface;
use KikCMS\Domain\DataTable\TableRow\TableViewRow;

readonly class DataTableRowService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DataTableDataService $dataService,
        private DataTableModifierService $dataTableModifierService,
    ) {}

    public function getRowView(mixed $rawRow, DataTable $dataTable, Filters $filters, string|int|null $id = null): TableViewRow
    {
        $id          = $id ?: $this->getId($rawRow, $dataTable);
        $filteredRow = $this->filterRowData($rawRow, $dataTable, $filters);

        $viewRow = new TableViewRow($id, $rawRow, $filteredRow);

        if ($modifier = $this->dataTableModifierService->resolve($dataTable, ViewRowDataModifierInterface::class)) {
            $viewRow = $modifier->modify($viewRow, $dataTable, $filters);
        }

        return $viewRow;
    }

    public function getId(array $row, DataTable $dataTable): string
    {
        if ($dataTable->getSource() == SourceType::Local) {
            return '';
        }

        $metaData = $this->entityManager->getClassMetadata($dataTable->getPdoModel());

        $identifierFieldNames = $metaData->getIdentifierFieldNames();

        $idParts = [];

        foreach ($identifierFieldNames as $fieldName) {
            $idParts[] = $row[$fieldName];
        }

        return implode(":", $idParts);
    }

    private function filterRowData(array $row, DataTable $dataTable, Filters $filters): array
    {
        $headerKeys = array_keys($dataTable->getHeaders());

        $filteredData = [];

        foreach ($headerKeys as $headerKey) {
            $cellType = $dataTable->getCells()[$headerKey]['type'] ?? null;

            $value = $this->dataService->resolveValue($row, $headerKey, $filters->getLangCode());
            $value = $this->transformValueByType($value, $cellType);

            $filteredData[] = $value;
        }

        return $filteredData;
    }

    private function transformValueByType(mixed $value, ?string $cellType): mixed
    {
        return match ($cellType) {
            DataTableConfig::CELL_TYPE_CHECKBOX => (bool) $value,
            default => $value,
        };
    }
}
