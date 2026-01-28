<?php

namespace KikCMS\Domain\DataTable\Rearrange;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Entity\Page\Page;

readonly class RearrangeService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function rearrange(DataTable $dataTable, int $sourceId, int $targetId, Location $location): void
    {
        $entityClass = $dataTable->getPdoModel();
        $repository  = $this->entityManager->getRepository($entityClass);

        /** @var Page $targetEntity */
        $targetEntity = $repository->find($targetId);

        /** @var Page $sourceEntity */
        $sourceEntity = $repository->find($sourceId);

        switch ($location) {
            case Location::BEFORE:
                $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);
                $this->nodesFromTargetPlusOne($dataTable, $targetEntity);

                $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
            break;

            case Location::AFTER:
                $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);

                if ($targetEntity->getDisplayOrder() > $sourceEntity->getDisplayOrder()) {
                    $this->nodesFromTargetPlusOne($dataTable, $targetEntity);
                    $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
                } else {
                    $this->nodesAfterTargetPlusOne($dataTable, $targetEntity);
                    $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder() + 1);
                }
            break;
        }

        $this->entityManager->persist($sourceEntity);
        $this->entityManager->flush();
    }

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

    /**
     * Modifies the display order of entities in bulk based on the specified operator and modification type.
     */
    public function bulkModifyOrder(DataTable $dataTable, object $entity, string $mod, string $operator): void
    {
        $query = $this->rearrangeService->getBulkModifyOrderQuery($dataTable, $entity, $mod, $operator);
        $query->getQuery()->execute();
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
}