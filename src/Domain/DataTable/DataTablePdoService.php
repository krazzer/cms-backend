<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\App\CallableService;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Config\DataTablePathService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Filter\DataTableFilterService;
use KikCMS\Domain\DataTable\Modifier\DataTableModifierService;
use KikCMS\Domain\DataTable\Modifier\RawTableDataModifierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;

readonly class DataTablePdoService
{
    public function __construct(
        private CallableService $callableService,
        private EntityManagerInterface $entityManager,
        private DataTableRowService $rowService,
        private DataTableDataService $dataService,
        private DataTableStoreService $dataTableStoreService,
        private DataTableFilterService $dataTableFilterService,
        private DataTablePathService $dataTablePathService,
        private DataTableModifierService $dataTableModifierService,
    ) {}

    public function getData(DataTable $dataTable, ?DataTableFilters $filters = null): array
    {
        if ( ! $filters) {
            $filters = new DataTableFilters();
        }

        $queryBuilder = $this->getQueryBuilder($dataTable);

        if ($queryCallable = $this->callableService->getCallableByString($dataTable->getQuery())) {
            call_user_func($queryCallable, $queryBuilder);
        }

        if ($filters) {
            $this->dataTableFilterService->filter($dataTable, $filters, $queryBuilder);
        }

        $rawData = $queryBuilder->getQuery()->getArrayResult();

        if ($modifier = $this->dataTableModifierService->resolve($dataTable, RawTableDataModifierInterface::class)) {
            $rawData = $modifier->modify($rawData, $dataTable, $filters);
        }

        $viewData = [];

        foreach ($rawData as $row) {
            $viewDataRow = $this->rowService->getRowView($row, $dataTable, $filters);
            $viewData[]  = $viewDataRow->toArray();
        }

        return $viewData;
    }

    public function getQueryBuilder(DataTable $dataTable): QueryBuilder
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        return $repository->createQueryBuilder(DataTableConfig::DEFAULT_TABLE_ALIAS);
    }

    public function getEditData(DataTable $dataTable, string $id): ?array
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        if ( ! $entity = $repository->find($id)) {
            return null;
        }

        $arrayData = $this->getEntityDataAsArray($dataTable->getPdoModel(), $entity);

        foreach ($dataTable->getFormFieldMap() as $key => $field) {
            if ($key === $field) {
                continue;
            }

            $value = $this->dataService->resolveValue($arrayData, $field, $dataTable->getLangCode());

            $arrayData[$key] = $value;
        }

        // remove all fields that are not required in the form
        return array_intersect_key($arrayData, array_flip($dataTable->getFormFieldKeys()));
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

                $value = $this->getValueByType($fieldMapping['type'], $value);
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

        $dataToStore = $this->dataTableStoreService->getDataArrayToStore($dataTable, $data);

        $this->updateEntityByArray($entity, $dataToStore);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function create(DataTable $dataTable, array $data): int
    {
        $model = $dataTable->getPdoModel();

        $entity = new $model();

        $dataToStore = $this->dataTableStoreService->getDataArrayToStore($dataTable, $data);

        $this->updateEntityByArray($entity, $dataToStore);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity->getId();
    }

    public function deleteList(DataTable $dataTable, array $ids): void
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        foreach ($ids as $id) {
            $entity = $repository->find($id);
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    public function updateCheckbox(DataTable $dataTable, int $id, string $field, bool $value): void
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        if ( ! $entity = $repository->find($id)) {
            throw new Exception('Object with id: ' . $id . ' not found');
        }

        $dataToStore = $this->dataTablePathService->convertPathToArray($field, $value, $dataTable->getLangCode());

        $this->updateEntityByArray($entity, $dataToStore);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function getValueByType(mixed $type, mixed $value): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'integer', 'smallint', 'bigint' => (int) $value,
            'float', 'decimal' => (float) $value,
            'string' => (string) $value,
            default => $value,
        };
    }

}
