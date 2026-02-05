<?php

namespace KikCMS\Domain\DataTable\Tree;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Rearrange\AbstractRearrangeService;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Entity\Page\Page;

readonly class TreeRearrangeService extends AbstractRearrangeService
{
    public function rearrange(DataTable $dataTable, int $sourceId, int $targetId, Location $location): void
    {
        list($sourceEntity, $targetEntity) = $this->getEntities($dataTable, $sourceId, $targetId);

        // cannot place a parent into its own child
        if ($targetEntity->getParents() && in_array($sourceEntity->getId(), $targetEntity->getParents())) {
            return;
        }

        // pre-fetch parents for updating the child nodes
        $oldChildParents = $this->getParentsValueInsideNode($sourceEntity);

        switch ($location) {
            case Location::BEFORE:
                $this->rearrangeBefore($dataTable, $sourceEntity, $targetEntity);
            break;
            case Location::AFTER:
                $this->rearrangeAfter($dataTable, $sourceEntity, $targetEntity);
            break;
            case Location::INSIDE:
                $this->rearrangeInside($dataTable, $sourceEntity, $targetEntity);
            break;
        }

        $newChildParents = $this->getParentsValueInsideNode($sourceEntity);

        $this->updateChildParents($dataTable, $oldChildParents, $newChildParents);

        $this->entityManager->persist($sourceEntity);
        $this->entityManager->flush();
    }

    /**
     * Modifies the display order of entities in bulk based on the specified operator and modification type.
     */
    public function bulkModifyOrder(DataTable $dataTable, object $entity, string $mod, string $operator): void
    {
        $query = $this->getBulkModifyOrderQuery($dataTable, $entity, $mod, $operator);

        if ($entity->getParents()) {
            $query->andWhere('e.parents = :parents')
                ->setParameter('parents', json_encode($entity->getParents()));
        } else {
            $query->andWhere('e.parents IS NULL');
        }

        $query->getQuery()->execute();
    }

    public function getTargetChildMaxDisplayOrder(Page $targetEntity): int
    {
        $parents = $this->getParentsValueInsideNode($targetEntity);

        $query = $this->entityManager->createQueryBuilder()
            ->select('MAX(e.' . DataTableConfig::DISPLAY_ORDER . ')')
            ->from(Page::class, DataTableConfig::DEFAULT_TABLE_ALIAS)
            ->where('e.parents = :parents')
            ->setParameter('parents', json_encode($parents));

        $max = (int) $query->getQuery()->getSingleScalarResult();

        return $max ?: 0;
    }

    public function getParentsValueInsideNode(Page $targetEntity): array
    {
        if ($targetEntity->getParents()) {
            return array_merge($targetEntity->getParents(), [$targetEntity->getId()]);
        } else {
            return [$targetEntity->getId()];
        }
    }

    public function updateChildParents(DataTable $dataTable, array $oldParents, array $newParents): void
    {
        $meta = $this->entityManager->getClassMetadata($dataTable->getPdoModel());

        $search  = rtrim(json_encode($oldParents), ']');
        $replace = rtrim(json_encode($newParents), ']');

        $sql = "UPDATE " . $meta->getTableName() . " 
            SET parents = REPLACE(parents, :search, :replace) 
            WHERE parents LIKE :likePrefix OR parents = :match";

        $this->entityManager->getConnection()->executeStatement($sql, [
            'search'     => $search,
            'replace'    => $replace,
            'likePrefix' => $search . ',%',
            'match'      => $search . ']',
        ]);
    }

    private function rearrangeBefore(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);
        $this->nodesFromTargetPlusOne($dataTable, $targetEntity);

        $sourceEntity->setParents($targetEntity->getParents());
        $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
    }

    private function rearrangeAfter(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);

        if ($targetEntity->getParents() == $sourceEntity->getParents() && $targetEntity->getDisplayOrder() > $sourceEntity->getDisplayOrder()) {
            $this->nodesFromTargetPlusOne($dataTable, $targetEntity);
            $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
        } else {
            $this->nodesAfterTargetPlusOne($dataTable, $targetEntity);
            $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder() + 1);
        }

        $sourceEntity->setParents($targetEntity->getParents());
    }

    private function rearrangeInside(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);

        $parents = $this->getParentsValueInsideNode($targetEntity);
        $order   = $this->getTargetChildMaxDisplayOrder($targetEntity);

        $sourceEntity->setParents($parents);
        $sourceEntity->setDisplayOrder($order + 1);
    }
}