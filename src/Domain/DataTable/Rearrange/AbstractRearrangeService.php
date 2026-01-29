<?php

namespace KikCMS\Domain\DataTable\Rearrange;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;

readonly class AbstractRearrangeService
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {}

    /**
     * Modifies the display order of entities in bulk based on the specified operator and modification type.
     */
    public function getBulkModifyOrderQuery(DataTable $dataTable, object $entity, string $mod, string $operator): QueryBuilder
    {
        $entityClass = $dataTable->getPdoModel();

        return $this->entityManager->createQueryBuilder()
            ->update($entityClass, DataTableConfig::DEFAULT_TABLE_ALIAS)
            ->set('e.display_order', 'e.display_order ' . $mod . ' 1')
            ->where('e.display_order ' . $operator . ' :order')
            ->setParameter('order', $entity->getDisplayOrder());
    }

    public function getEntities(DataTable $dataTable, int $sourceId, int $targetId): array
    {
        $entityClass = $dataTable->getPdoModel();
        $repository  = $this->entityManager->getRepository($entityClass);

        $sourceEntity = $repository->find($sourceId);
        $targetEntity = $repository->find($targetId);

        return [$sourceEntity, $targetEntity];
    }

    /**
     * Do a -1 display order after the source entity
     */
    public function nodesAfterSourceMinusOne(DataTable $dataTable, object $entity): void
    {
        $this->bulkModifyOrder($dataTable, $entity, '-', '>');
    }

    /**
     * Increment the display order of nodes from the target entity by 1.
     */
    public function nodesFromTargetPlusOne(DataTable $dataTable, object $entity): void
    {
        $this->bulkModifyOrder($dataTable, $entity, '+', '>=');
    }

    /**
     * Increment the display order of nodes after the target entity by 1.
     */
    public function nodesAfterTargetPlusOne(DataTable $dataTable, object $entity): void
    {
        $this->bulkModifyOrder($dataTable, $entity, '+', '>');
    }

    /**
     * Modifies the display order of entities in bulk based on the specified operator and modification type.
     */
    public function bulkModifyOrder(DataTable $dataTable, object $entity, string $mod, string $operator): void
    {
        $query = $this->getBulkModifyOrderQuery($dataTable, $entity, $mod, $operator);
        $query->getQuery()->execute();
    }
}