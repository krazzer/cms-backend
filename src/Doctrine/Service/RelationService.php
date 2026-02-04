<?php

namespace KikCMS\Doctrine\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ManyToOneAssociationMapping;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;

readonly class RelationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getOneToManyRelationField(string $parentClass, string $childClass): ?string
    {
        if ($mapping = $this->getOneToManyRelationMapping($parentClass, $childClass)) {
            return $mapping['fieldName'];
        }

        return null;
    }

    public function getOneToManyRelationMapping(string $parentClass, string $childClass): ?ManyToOneAssociationMapping
    {
        $childMeta = $this->entityManager->getClassMetadata($childClass);

        return array_find($childMeta->associationMappings, fn($mapping) => (
            $mapping['type'] === ClassMetadata::MANY_TO_ONE && $mapping['targetEntity'] === $parentClass));
    }

    public function hasOneToManyRelation(string $parentClass, string $childClass): bool
    {
        if ($this->getOneToManyRelationMapping($parentClass, $childClass)) {
            return true;
        } else {
            return false;
        }
    }

    public function hasUnSavedParentDataTable(DataTable $dataTable, ?DataTableFilters $filters): bool
    {
        if ( ! $filters || $filters->getParentId()) {
            return false;
        }

        if ( ! $parentDataTable = $filters->getParentDataTable()) {
            return false;
        }

        if ( ! ($parentModel = $parentDataTable->getPdoModel()) || ! ($childModel = $dataTable->getPdoModel())) {
            return false;
        }

        return $this->hasOneToManyRelation($parentModel, $childModel);
    }
}