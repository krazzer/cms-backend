<?php

namespace KikCMS\Domain\DataTable\Dto\Denormalizer;


use KikCMS\Domain\DataTable\DataTableLanguageResolver;
use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class FiltersDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private DataTableLanguageResolver $dataTableLanguageResolver,
        private DataTableService $dataTableService
    ) {}

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === DataTableFilters::class && is_array($data);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): DataTableFilters
    {
        $filters = new DataTableFilters();

        if ($data['search'] ?? false) $filters->setSearch($data['search']);
        if ($data['sort'] ?? false) $filters->setSort($data['sort']);
        if ($data['sortDirection'] ?? false) $filters->setSortDirection($data['sortDirection']);
        if ($data['page'] ?? false) $filters->setPage($data['page']);
        if ($data['filters'] ?? false) $filters->setFilters($data['filters']);
        if ($data['parentEditId'] ?? false) $filters->setParentId($data['parentEditId']);

        if ($instance = ($data['parentInstance'] ?? false)) {
            $dataTable = $this->dataTableService->getByInstance($instance);
            $filters->setParentDataTable($dataTable);
        }

        $langCode = $this->dataTableLanguageResolver->resolve($data['lang'] ?? null);
        $filters->setLangCode($langCode);

        return $filters;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DataTableFilters::class => true];
    }
}