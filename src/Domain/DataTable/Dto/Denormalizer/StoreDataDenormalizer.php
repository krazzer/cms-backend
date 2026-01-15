<?php

namespace KikCMS\Domain\DataTable\Dto\Denormalizer;


use KikCMS\Domain\DataTable\Object\DataTableStoreData;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class StoreDataDenormalizer implements DenormalizerInterface
{
    public function __construct() {}

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === DataTableStoreData::class && is_array($data);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): DataTableStoreData
    {
        return new DataTableStoreData($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DataTableStoreData::class => true];
    }
}