<?php

namespace App\Entity\DataTable;

use Doctrine\ORM\EntityManagerInterface;

class DataTablePdoService
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param DataTable $dataTable
     * @return array
     */
    public function getData(DataTable $dataTable): array
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        return $repository->createQueryBuilder('e')
            ->getQuery()
            ->getArrayResult();
    }
}