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

        if($location == Location::BEFORE) {
            $this->rearrangeBefore($dataTable, $sourceEntity, $targetEntity);
        } elseif($location == Location::AFTER) {
            $this->rearrangeAfter($dataTable, $sourceEntity, $targetEntity);
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

    private function rearrangeAfter(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
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