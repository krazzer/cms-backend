<?php

namespace App\Domain\DataTable;

readonly class DataTableDataService
{
    public function __construct(
        private DataTableConfigService $configService
    ) {}

    public function resolveValue(array $data, string $path, string $langCode): mixed
    {
        if (array_key_exists($path, $data)) {
            return $data[$path];
        }

        if (str_contains($path, '.')) {
            return $this->configService->getDataByPath($data, $path, $langCode);
        }

        return '';
    }
}