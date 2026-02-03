<?php

namespace KikCMS\Domain\DataTable\Config;

class DataTablePathService
{
    public function isPath(string $path): bool
    {
        return str_contains($path, DataTableConfig::PATH_SEPARATOR);
    }

    public function getDataByPath(array $data, string $path, string $locale): mixed
    {
        $keys  = $this->pathToKeys($path, $locale);
        $value = $data;

        foreach ($keys as $key) {
            if ( ! is_array($value) || ! array_key_exists($key, $value)) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }

    public function convertPathToArray(string $path, mixed $value, string $locale): array
    {
        $keys = $this->pathToKeys($path, $locale);

        $result = [];
        $ref    =& $result;

        foreach ($keys as $key) {
            $ref =& $ref[$key];
        }

        $ref = $value;

        return $result;
    }

    public function toJson(mixed $column, string $locale): array
    {
        $parts = $this->pathToKeys($column, $locale);

        return [$parts[0], '$.' . implode('.', array_slice($parts, 1))];
    }

    private function pathToKeys(string $path, string $locale): array
    {
        return explode(DataTableConfig::PATH_SEPARATOR, $this->replaceLocale($path, $locale));
    }

    private function replaceLocale(string $path, string $locale): string
    {
        return str_replace(DataTableConfig::PATH_LOCALE, $locale, $path);
    }
}