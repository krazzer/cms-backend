<?php

namespace App\Domain\DataTable;

use App\Entity\Page\Page;
use Doctrine\ORM\EntityManagerInterface;

readonly class DataTableRowService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DataTableConfigService $configService,
    ) {}

    public function getRowData(mixed $row, DataTable $dataTable): array
    {
        $id = $this->getId($row, $dataTable);

        $headers      = array_keys($dataTable->getHeaders());
        $filteredData = $this->filterRowData($row, $headers, $dataTable->getLangCode());

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

    private function filterRowData(array $row, array $headers, string $langCode): array
    {
        $filteredData = [];

        foreach ($headers as $header) {
            if (array_key_exists($header, $row)) {
                $value = $row[$header];
            } else {
                if (str_contains($header, '.')) {
                    $value = $this->configService->getDataByPath($row, $header, $langCode);
                } else {
                    $value = '';
                }
            }

            $filteredData[] = $value;
        }

        return $filteredData;
    }
}
