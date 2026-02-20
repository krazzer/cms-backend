<?php

namespace KikCMS\Domain\DataTable\Dto\Denormalizer;


use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\DataTableService;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class DataTableDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private DataTableService $dataTableService
    ) {}

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === DataTable::class && is_string($data);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): DataTable
    {
        return $this->dataTableService->getByInstance((string) $data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DataTable::class => true];
    }
}