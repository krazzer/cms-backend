<?php

namespace App\Domain\DataTable;

use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class DataTablePdoService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DataTableRowService $dataTableRowService,
    ) {}

    public function getData(DataTable $dataTable): array
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        $rawData = $repository->createQueryBuilder('e')
            ->getQuery()
            ->getArrayResult();

        $returnData = [];

        foreach ($rawData as $row) {
            $returnData[] = $this->dataTableRowService->getRowData($row, $dataTable);
        }

        return $returnData;
    }

    public function getEditData(DataTable $dataTable, string $id): ?array
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        if ( ! $entity = $repository->find($id)) {
            return null;
        }

        $arrayData = $this->getEntityDataAsArray($dataTable->getPdoModel(), $entity);

        // remove all fields not required in the form
        return array_intersect_key($arrayData, array_flip($dataTable->getFormFields()));
    }

    public function getEntityDataAsArray(string $model, object $entity): array
    {
        $metadata = $this->entityManager->getClassMetadata($model);

        $data = [];

        foreach ($metadata->getFieldNames() as $field) {
            $getter   = 'get' . ucfirst($field);
            $isGetter = 'is' . ucfirst($field);

            if (method_exists($entity, $isGetter)) {
                $data[$field] = $entity->$isGetter();
            } elseif (method_exists($entity, $getter)) {
                $data[$field] = $entity->$getter();
            } else {
                $data[$field] = null;
            }
        }

        return $data;
    }

    public function updateEntityByArray(object $entity, array $data): void
    {
        $className = get_class($entity);
        $metadata  = $this->entityManager->getClassMetadata($className);

        foreach ($data as $field => $value) {
            $setter = 'set' . ucfirst($field);

            if ( ! method_exists($entity, $setter)) {
                throw new Exception("Setter method $setter does not exist on " . $className);
            }

            // Check if $field een mapped field is in Doctrine
            if ($metadata->hasField($field)) {
                $fieldMapping = $metadata->getFieldMapping($field);

                $type = $fieldMapping['type'];

                switch ($type) {
                    case 'boolean':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    break;

                    case 'integer':
                    case 'smallint':
                    case 'bigint':
                        $value = (int) $value;
                    break;

                    case 'float':
                    case 'decimal':
                        $value = (float) $value;
                    break;

                    case 'string':
                        $value = (string) $value;
                    break;
                }
            }

            $entity->$setter($value);
        }
    }

    public function update(DataTable $dataTable, string $id, array $data): void
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        if ( ! $entity = $repository->find($id)) {
            throw new Exception('Object with id: ' . $id . ' not found');
        }

        $this->updateEntityByArray($entity, $data);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function create(DataTable $dataTable, array $data): void
    {
        $model = $dataTable->getPdoModel();

        $entity = new $model();

        $this->updateEntityByArray($entity, $data);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
