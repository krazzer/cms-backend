<?php

namespace KikCMS\Domain\DataTable\Dto\Denormalizer;


use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class FiltersDenormalizer implements DenormalizerInterface
{
    public function __construct() {}

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === DataTableFilters::class && is_array($data);
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): DataTableFilters
    {
        $filters = new DataTableFilters();

        if ($data['search']) $filters->setSearch($data['search']);
        if ($data['sort']) $filters->setSort($data['sort']);
        if ($data['sortDirection']) $filters->setSortDirection($data['sortDirection']);
        if ($data['page']) $filters->setPage($data['page']);
        if ($data['filters']) $filters->setFilters($data['filters']);

        return $filters;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DataTableFilters::class => true];
    }
}