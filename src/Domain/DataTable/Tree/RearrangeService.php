<?php

namespace App\Domain\DataTable\Tree;

use App\Domain\DataTable\DataTable;
use App\Entity\Page\Page;
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

        switch ($location) {
            case RearrangeLocation::BEFORE:
                $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);
                $this->nodesFromTargetPlusOne($dataTable, $targetEntity);

                $sourceEntity->setParents($targetEntity->getParents());
                $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
            break;

            case RearrangeLocation::AFTER:
                $this->nodesAfterSourceMinusOne($dataTable, $sourceEntity);

                if ($targetEntity->getParents() == $sourceEntity->getParents()) {
                    $this->nodesFromTargetPlusOne($dataTable, $targetEntity);
                    $sourceEntity->setDisplayOrder($targetEntity->getDisplayOrder());
                } else {
                    $this->nodesAfterTargetPlusOne($dataTable, $sourceEntity);
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
            ->update($entityClass, 'e')
            ->set('e.display_order', 'e.display_order ' . $mod . ' 1')
            ->where('e.parents = :parents AND e.display_order ' . $operator . ' :order')
            ->setParameter('order', $page->getDisplayOrder())
            ->setParameter('parents', json_encode($page->getParents()));

        $query->getQuery()->execute();
    }

    public function getTargetChildMaxDisplayOrder(Page $targetEntity): int
    {
        $parents = $this->getParentsValueInsideNode($targetEntity);

        $query = $this->entityManager->createQueryBuilder()
            ->select('MAX(e.display_order)')
            ->from(Page::class, 'e')
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
}