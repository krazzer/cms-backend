<?php

namespace App\Domain\DataTable;

use App\Entity\Page\Page;
use Doctrine\ORM\EntityManagerInterface;

readonly class DataTableRowService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DataTableDataService $dataService,
    ) {}

    public function getRowData(mixed $row, DataTable $dataTable): array
    {
        $id = $this->getId($row, $dataTable);

        $filteredData = $this->filterRowData($row, $dataTable);

        $rowData = ['id' => $id, 'data' => $filteredData];

        if ($dataTable instanceof PagesDataTable) {
            $rowData['level']    = count($row['parents'] ?? []);
            $rowData['type']     = $row[Page::FIELD_TYPE];
            $rowData['children'] = $row[Page::FIELD_CHILDREN];
        }

        return $rowData;
    }

    public function getId(array $row, DataTable $dataTable): string
    {
        $metaData = $this->entityManager->getClassMetadata($dataTable->getPdoModel());

        $identifierFieldNames = $metaData->getIdentifierFieldNames();

        $idParts = [];

        foreach ($identifierFieldNames as $fieldName) {
            $idParts[] = $row[$fieldName];
        }

        return implode(":", $idParts);
    }

    private function filterRowData(array $row, DataTable $dataTable): array
    {
        $langCode   = $dataTable->getLangCode();
        $headerKeys = array_keys($dataTable->getHeaders());

        $filteredData = [];

        foreach ($headerKeys as $headerKey) {
            $cellType = $dataTable->getCells()[$headerKey]['type'] ?? null;

            $value = $this->dataService->resolveValue($row, $headerKey, $langCode);
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
