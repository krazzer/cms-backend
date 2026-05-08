<?php

namespace KikCMS\Domain\DataTable\Tree;

use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Rearrange\AbstractRearrangeService;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Entity\Page\Page;
use KikCMS\Entity\Page\PageTreeService;

readonly class TreeRearrangeService extends AbstractRearrangeService
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private PageTreeService $pageTreeService,
    ) {
        parent::__construct($entityManager);
    }

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

    public function getTargetChildMaxDisplayOrder(Page $targetEntity): int
    {
        $parents = $this->getParentsValueInsideNode($targetEntity);

        return $this->pageTreeService->getMaxDisplayOrder($parents);
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

    protected function rearrangeBefore(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $sourceOrder = $sourceEntity->getDisplayOrder();
        $targetOrder = $targetEntity->getDisplayOrder();

        $sourceParents = $sourceEntity->getParents();
        $targetParents = $targetEntity->getParents();

        if ($sourceParents === $targetParents) {
            parent::rearrangeBefore($dataTable, $sourceEntity, $targetEntity);
        } else {
            $this->decrementRange($dataTable, $sourceOrder + 1, PHP_INT_MAX, $sourceParents);
            $this->incrementRange($dataTable, $targetOrder, PHP_INT_MAX, $targetParents);
            $sourceEntity->setDisplayOrder($targetOrder);
        }

        $sourceEntity->setParents($targetEntity->getParents());
    }

    protected function rearrangeAfter(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $sourceOrder = $sourceEntity->getDisplayOrder();
        $targetOrder = $targetEntity->getDisplayOrder();

        $sourceParents = $sourceEntity->getParents();
        $targetParents = $targetEntity->getParents();

        if ($sourceParents === $targetParents) {
            parent::rearrangeAfter($dataTable, $sourceEntity, $targetEntity);
        } else {
            $this->decrementRange($dataTable, $sourceOrder + 1, PHP_INT_MAX, $sourceParents);
            $this->incrementRange($dataTable, $targetOrder + 1, PHP_INT_MAX, $targetParents);
            $sourceEntity->setDisplayOrder($targetOrder + 1);
        }

        $sourceEntity->setParents($targetParents);
    }

    private function rearrangeInside(DataTable $dataTable, mixed $sourceEntity, mixed $targetEntity): void
    {
        $sourceOrder = $sourceEntity->getDisplayOrder();

        $this->decrementRange($dataTable, $sourceOrder + 1, PHP_INT_MAX, $sourceEntity->getParents());

        $parents  = $this->getParentsValueInsideNode($targetEntity);
        $maxOrder = $this->getTargetChildMaxDisplayOrder($targetEntity);

        $sourceEntity->setParents($parents);
        $sourceEntity->setDisplayOrder($maxOrder + 1);
    }
}