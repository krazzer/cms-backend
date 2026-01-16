<?php

namespace KikCMS\Domain\DataTable\Tree;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Entity\Page\Page;
use Doctrine\ORM\EntityManagerInterface;

readonly class RearrangeService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function rearrange(DataTable $dataTable, int $sourceId, int $targetId, RearrangeLocation $location): void
    {
        $entityClass = $dataTable->getPdoModel();
        $repository  = $this->entityManager->getRepository($entityClass);

        /** @var Page $targetEntity */
        $targetEntity = $repository->find($targetId);

        /** @var Page $sourceEntity */
        $sourceEntity = $repository->find($sourceId);

        // cannot place a parent into its own child
        if ($targetEntity->getParents() && in_array($sourceEntity->getId(), $targetEntity->getParents())) {
            return;
        }

        // pre-fetch parents for updating the child nodes
        $oldChildParents = $this->getParentsValueInsideNode($sourceEntity);

        switch ($location) {
            case RearrangeLocation::BEFORE:
                $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);
                $this->nodesFromTargetPlusOne($dataTable, $targetEntity);

                $sourceEntity->setParents($targetEntity->getParents());
                $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
            break;

            case RearrangeLocation::AFTER:
                $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);

                if ($targetEntity->getParents() == $sourceEntity->getParents() && $targetEntity->getDisplayOrder() > $sourceEntity->getDisplayOrder()) {
                    $this->nodesFromTargetPlusOne($dataTable, $targetEntity);
                    $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
                } else {
                    $this->nodesAfterTargetPlusOne($dataTable, $targetEntity);
                    $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder() + 1);
                }

                $sourceEntity->setParents($targetEntity->getParents());
            break;
            case RearrangeLocation::INSIDE:
                $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);

                $parents = $this->getParentsValueInsideNode($targetEntity);
                $order   = $this->getTargetChildMaxDisplayOrder($targetEntity);

                $sourceEntity->setParents($parents);
                $sourceEntity->setDisplayOrder($order + 1);
        }

        $newChildParents = $this->getParentsValueInsideNode($sourceEntity);

        $this->updateChildParents($dataTable, $oldChildParents, $newChildParents);

        $this->entityManager->persist($sourceEntity);
        $this->entityManager->flush();
    }

    /**
     * Do a -1 display order after the source entity
     */
    public function nodesAfterSourceMinusOne(DataTable $dataTable, Page $sourceEntity): void
    {
        $this->bulkModifyOrder($dataTable, $sourceEntity, '-', '>');
    }

    /**
     * Increment the display order of nodes from the target entity by 1.
     */
    public function nodesFromTargetPlusOne(DataTable $dataTable, Page $targetEntity): void
    {
        $this->bulkModifyOrder($dataTable, $targetEntity, '+', '>=');
    }

    /**
     * Increment the display order of nodes after the target entity by 1.
     */
    public function nodesAfterTargetPlusOne(DataTable $dataTable, Page $targetEntity): void
    {
        $this->bulkModifyOrder($dataTable, $targetEntity, '+', '>');
    }

    /**
     * Modifies the display order of entities in bulk based on the specified operator and modification type.
     */
    public function bulkModifyOrder(DataTable $dataTable, Page $page, string $mod, string $operator): void
    {
        $entityClass = $dataTable->getPdoModel();

        $query = $this->entityManager->createQueryBuilder()
            ->update($entityClass, DataTableConfig::DEFAULT_TABLE_ALIAS)
            ->set('e.display_order', 'e.display_order ' . $mod . ' 1')
            ->where('e.display_order ' . $operator . ' :order')
            ->setParameter('order', $page->getDisplayOrder());

        if ($page->getParents()) {
            $query->andWhere('e.parents = :parents')
                ->setParameter('parents', json_encode($page->getParents()));
        } else {
            $query->andWhere('e.parents IS NULL');
        }

        $query->getQuery()->execute();
    }

    public function getTargetChildMaxDisplayOrder(Page $targetEntity): int
    {
        $parents = $this->getParentsValueInsideNode($targetEntity);

        $query = $this->entityManager->createQueryBuilder()
            ->select('MAX(e.display_order)')
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
}