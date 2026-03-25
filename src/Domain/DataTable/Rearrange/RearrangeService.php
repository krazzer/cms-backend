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
}