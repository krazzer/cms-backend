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

        $rawData = $repository->createQueryBuilder('e')
            ->getQuery()
            ->getArrayResult();

        $returnData = [];

        foreach ($rawData as $row) {
            $id = $this->getId($row, $dataTable);

            // Filter data to only get the fields needed by header
            $headers      = array_keys($dataTable->getHeaders());
            $filteredData = array_map(fn($key) => $row[$key] ?? null, $headers);

            $returnData[] = ['id' => $id, 'data' => $filteredData];
        }

        return $returnData;
    }

    /**
     * @param array $row
     * @param DataTable $dataTable
     * @return string
     */
    public function getId(array $row, DataTable $dataTable): string
    {
        $metaData = $this->entityManager->getClassMetadata($dataTable->getPdoModel());

        $identifierFieldNames = $metaData->getIdentifierFieldNames();

        $idParts = [];

        foreach ($identifierFieldNames as $fieldName) {
            $idParts[] = $row[$fieldName];
        }

        return implode(":", $idParts);
    }

    /**
     * @param DataTable $dataTable
     * @param string $id
     * @return array|null
     */
    public function getEditData(DataTable $dataTable, string $id): ?array
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        if ( ! $entity = $repository->find($id)) {
            return null;
        }

        return $this->getEntityDataAsArray($dataTable->getPdoModel(), $entity);
    }

    /**
     * @param string $model
     * @param object $entity
     * @return array
     */
    public function getEntityDataAsArray(string $model, object $entity): array
    {
        $metadata = $this->entityManager->getClassMetadata($model);

        $data = [];

        foreach ($metadata->getFieldNames() as $field) {
            $getter = 'get' . ucfirst($field);

            if (method_exists($entity, $getter)) {
                $data[$field] = $entity->$getter();
            } else {
                $data[$field] = null;
            }
        }

        return $data;
    }
}