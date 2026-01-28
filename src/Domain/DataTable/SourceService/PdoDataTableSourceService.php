<?php

namespace KikCMS\Domain\DataTable\SourceService;

use KikCMS\Domain\App\CallableService;
use KikCMS\Domain\App\Exception\ObjectNotFoundHttpException;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Config\DataTablePathService;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\DataTableDataService;
use KikCMS\Domain\DataTable\DataTableRowService;
use KikCMS\Domain\DataTable\DataTableStoreService;
use KikCMS\Domain\DataTable\Field\FieldService;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use KikCMS\Domain\DataTable\Filter\DataTableFilters as Filters;
use KikCMS\Domain\DataTable\Filter\DataTableFilterService;
use KikCMS\Domain\DataTable\Modifier\DataTableModifierService;
use KikCMS\Domain\DataTable\Modifier\RawTableDataModifierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use KikCMS\Domain\DataTable\Object\DataTableStoreData as StoreData;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;
use KikCMS\Domain\DataTable\Rearrange\RearrangeService;

readonly class PdoDataTableSourceService implements DataTableSourceServiceInterface
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
        private FieldService $fieldService, private RearrangeService $rearrangeService,
    ) {}

    public function getData(DataTable $dataTable, DataTableFilters $filters, ?StoreData $storeData = null): array
    {
        $queryBuilder = $this->getQueryBuilder($dataTable);

        if ($queryCallable = $this->callableService->getCallableByString($dataTable->getQuery())) {
            call_user_func($queryCallable, $queryBuilder);
        }

        $this->dataTableFilterService->filter($dataTable, $filters, $queryBuilder);

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

    public function getEditData(DataTable $dataTable, Filters $filters, string $id, StoreData $storeData): array
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());
        $fieldMap   = $this->fieldService->getFieldMap($dataTable);

        if ( ! $entity = $repository->find($id)) {
            throw new ObjectNotFoundHttpException("Object with id: $id not found");
        }

        $arrayData  = $this->getEntityDataAsArray($dataTable->getPdoModel(), $entity);
        $returnData = $arrayData;

        foreach ($fieldMap as $key => $field) {
            if ($key === $field->getField()) {
                continue;
            }

            $value = $this->dataService->resolveValue($arrayData, $field->getField(), $filters->getLangCode());

            $returnData[$key] = $value;
        }

        // remove all fields that are not required in the form
        return array_intersect_key($returnData, array_flip(array_keys($fieldMap)));
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

    public function update(DataTable $dataTable, Filters $filters, string $id, array $updateData, StoreData $storeData): void
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        if ( ! $entity = $repository->find($id)) {
            throw new Exception('Object with id: ' . $id . ' not found');
        }

        $dataToStore = $this->dataTableStoreService->getDataArrayToStore($dataTable, $filters, $updateData);

        $this->updateEntityByArray($entity, $dataToStore);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function create(DataTable $dataTable, Filters $filters, array $createData, StoreData $storeData): int
    {
        $model = $dataTable->getPdoModel();

        $entity = new $model();

        $dataToStore = $this->dataTableStoreService->getDataArrayToStore($dataTable, $filters, $createData);

        $this->updateEntityByArray($entity, $dataToStore);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity->getId();
    }

    public function deleteList(DataTable $dataTable, array $ids, StoreData $storeData): void
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        foreach ($ids as $id) {
            $entity = $repository->find($id);
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    public function updateCheckbox(DataTable $dataTable, Filters $filters, int $id, string $field, bool $value, StoreData $storeData): void
    {
        $repository = $this->entityManager->getRepository($dataTable->getPdoModel());

        if ( ! $entity = $repository->find($id)) {
            throw new Exception('Object with id: ' . $id . ' not found');
        }

        $dataToStore = $this->dataTablePathService->convertPathToArray($field, $value, $filters->getLangCode());

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

    public function rearrange(DataTable $dataTable, int $source, int $target, Location $location, StoreData $storeData): void
    {
        $this->rearrangeService->rearrange($dataTable, $source, $target, $location);
    }
}
