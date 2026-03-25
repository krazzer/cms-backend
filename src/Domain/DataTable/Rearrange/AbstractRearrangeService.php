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
    public function getModifyRangeQuery(DataTable $dt, string $mod, int $from, int $to, ?array $parents = null): QueryBuilder
    {
        $query = $this->entityManager->createQueryBuilder()
            ->update($dt->getPdoModel(), DataTableConfig::DEFAULT_TABLE_ALIAS)
            ->set('e.' . DataTableConfig::DISPLAY_ORDER, 'e.' . DataTableConfig::DISPLAY_ORDER . ' ' . $mod . ' 1')
            ->where('e.' . DataTableConfig::DISPLAY_ORDER . ' BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        if ($parents !== null) {
            $query->andWhere('e.parents = :parents')
                ->setParameter('parents', json_encode($parents));
        }

        return $query;
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
     * Do a +1 display order for a range
     */
    public function incrementRange(DataTable $dataTable, int $from, int $to, ?array $parents = null): void
    {
        $query = $this->getModifyRangeQuery($dataTable, '+', $from, $to, $parents);
        $query->getQuery()->execute();
    }

    /**
     * Do a +1 display order for a range
     */
    public function decrementRange(DataTable $dataTable, int $from, int $to, ?array $parents = null): void
    {
        $query = $this->getModifyRangeQuery($dataTable, '-', $from, $to, $parents);
        $query->getQuery()->execute();
    }

    protected function rearrangeBefore(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $targetOrder = $targetEntity->getDisplayOrder();
        $sourceOrder = $sourceEntity->getDisplayOrder();

        if ($targetOrder > $sourceOrder) {
            $this->decrementRange($dataTable, $sourceOrder + 1, $targetOrder - 1);
            $sourceEntity->setDisplayOrder($targetOrder - 1);
        } else {
            $this->incrementRange($dataTable, $targetOrder, $sourceOrder);
            $sourceEntity->setDisplayOrder($targetOrder);
        }
    }

    protected function rearrangeAfter(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $sourceOrder = $sourceEntity->getDisplayOrder();
        $targetOrder = $targetEntity->getDisplayOrder();

        if ($targetOrder > $sourceOrder) {
            $this->decrementRange($dataTable, $sourceOrder + 1, $targetOrder);
            $sourceEntity->setDisplayOrder($targetOrder);
        } else {
            $this->incrementRange($dataTable, $targetOrder + 1, $sourceOrder - 1);
            $sourceEntity->setDisplayOrder($targetOrder + 1);
        }
    }
}