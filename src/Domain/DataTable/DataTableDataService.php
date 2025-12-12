<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Config\DataTablePathService;

readonly class DataTableDataService
{
    public function __construct(
        private DataTablePathService $pathService
    ) {}

    public function resolveValue(array $data, string $path, string $langCode): mixed
    {
        if (array_key_exists($path, $data)) {
            return $data[$path];
        }

        if ($this->pathService->isPath($path)) {
            return $this->pathService->getDataByPath($data, $path, $langCode);
        }

        return '';
    }
}