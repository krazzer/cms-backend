<?php

namespace KikCMS\Domain\DataTable\Delete;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use KikCMS\Doctrine\Service\EntityService;
use KikCMS\Domain\DataTable\DataTable;
use ReflectionProperty;

readonly class DeleteImpactCalculator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntityService $entityService
    ) {}

    public function inspect(DataTable $dataTable, array $ids): array
    {
        $entities = $this->entityService->getByIds($dataTable->getPdoModel(), $ids);

        $result = [];

        foreach ($entities as $entity) {
            $this->walk($entity, $result, true);
        }

        return $result;
    }

    private function walk(object $entity, array &$result, bool $isRoot = false): void
    {
        $class = get_class($entity);
        $meta  = $this->entityManager->getClassMetadata($class);

        foreach ($meta->associationMappings as $mapping) {
            if ( ! $this->hasCascadeRemove($mapping)) {
                continue;
            }

            if ($this->hasSilentDeleteAttribute($mapping, $class)) {
                continue;
            }

            $subEntities = $this->getSubEntities($meta, $entity, $mapping);

            foreach ($subEntities as $subEntity) {
                $this->walk($subEntity, $result);
            }
        }

        if ( ! $isRoot) {
            $result[$class] = ($result[$class] ?? 0) + 1;
        }
    }

    private function hasCascadeRemove($mapping): bool
    {
        return in_array('remove', $mapping['cascade'], true) || ($mapping['orphanRemoval'] ?? false);
    }

    private function getSubEntities(ClassMetadata $meta, object $entity, $mapping): iterable
    {
        $subEntities = $meta->getFieldValue($entity, $mapping['fieldName']);

        if ($subEntities instanceof Collection || is_array($subEntities)) {
            return $subEntities;
        } elseif (is_object($subEntities)) {
            return [$subEntities];
        }

        return [];
    }

    private function hasSilentDeleteAttribute($mapping, string $class): bool
    {
        $reflection = new ReflectionProperty($class, $mapping['fieldName']);

        $warnAttributes = $reflection->getAttributes(SilentDelete::class);

        return ! empty($warnAttributes);
    }
}