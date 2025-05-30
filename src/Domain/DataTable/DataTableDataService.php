<?php

namespace App\Domain\DataTable;

readonly class DataTableDataService
{
    public function __construct(
        private DataTableConfigService $configService
    ) {}

    public function resolveValue(array $data, string $key, string $langCode): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        if (str_contains($key, '.')) {
            return $this->configService->getDataByPath($data, $key, $langCode);
        }

        return '';
    }
}