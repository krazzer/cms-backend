<?php

namespace KikCMS\Domain\DataTable\Rearrange;

use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Domain\DataTable\Config\DataTableConfig;

readonly class RearrangeIntegrityService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function check(string $model): void
    {
        if ( ! $this->hasDoubles($model)) {
            return;
        }

        $this->fix($model);
    }

    public function hasDoubles(string $model): bool
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('e.' . DataTableConfig::DISPLAY_ORDER . ', COUNT(e.' . DataTableConfig::ID . ')')
            ->from($model, 'e')
            ->groupBy('e.' . DataTableConfig::DISPLAY_ORDER)
            ->having('COUNT(e.id) > 1');

        return (bool) $query->getQuery()->getArrayResult();
    }

    private function fix(string $model): void
    {
        $idList = $this->getOrderedIds($model);
        $table  = $this->entityManager->getClassMetadata($model)->getTableName();

        $fieldId    = DataTableConfig::ID;
        $fieldOrder = DataTableConfig::DISPLAY_ORDER;

        $cases = [];

        foreach ($idList as $index => $id) {
            $cases[] = "WHEN " . (int) $id . " THEN " . ($index + 1);
        }

        $casesSql = implode(' ', $cases);
        $inSql    = implode(',', $idList);

        $this->entityManager->getConnection()->executeStatement("
            UPDATE $table SET $fieldOrder = CASE $fieldId $casesSql END WHERE $fieldId IN ($inSql)
        ");
    }

    private function getOrderedIds(string $model): array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('e.' . DataTableConfig::ID)
            ->from($model, 'e')
            ->orderBy('e.' . DataTableConfig::DISPLAY_ORDER);

        return $query->getQuery()->getSingleColumnResult();
    }
}