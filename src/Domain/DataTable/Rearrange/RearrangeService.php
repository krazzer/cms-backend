<?php

namespace KikCMS\Domain\DataTable\Rearrange;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;

readonly class RearrangeService extends AbstractRearrangeService
{
    public function rearrange(DataTable $dataTable, int $sourceId, int $targetId, Location $location): void
    {
        list($sourceEntity, $targetEntity) = $this->getEntities($dataTable, $sourceId, $targetId);

        switch ($location) {
            case Location::BEFORE:
                $this->rearrangeBefore($dataTable, $sourceEntity, $targetEntity);
            break;
            case Location::AFTER:
                $this->rearrangeAfter($dataTable, $sourceEntity, $targetEntity);
            break;
            case RearrangeLocation::INSIDE:
        }

        $this->entityManager->persist($sourceEntity);
        $this->entityManager->flush();
    }

    public function getMaxDisplayOrder(string $model): int
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('MAX(e.' . DataTableConfig::DISPLAY_ORDER . ')')
            ->from($model, 'e');

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    private function rearrangeBefore(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);
        $this->nodesFromTargetPlusOne($dataTable, $targetEntity);

        $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
    }

    private function rearrangeAfter(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);

        if ($targetEntity->getDisplayOrder() > $sourceEntity->getDisplayOrder()) {
            $this->nodesFromTargetPlusOne($dataTable, $targetEntity);
            $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
        } else {
            $this->nodesAfterTargetPlusOne($dataTable, $targetEntity);
            $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder() + 1);
        }
    }
}