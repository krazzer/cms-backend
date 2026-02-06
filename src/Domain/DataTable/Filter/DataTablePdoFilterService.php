<?php

namespace KikCMS\Domain\DataTable\Filter;


use KikCMS\Doctrine\Service\RelationService;
use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Config\DataTablePathService;
use KikCMS\Domain\DataTable\DataTable;
use Doctrine\ORM\QueryBuilder;
use KikCMS\Domain\DataTable\DataTableLanguageResolver;

readonly class DataTablePdoFilterService
{
    public function __construct(
        private DataTablePathService $pathService,
        private DataTableLanguageResolver $dataTableLanguageResolver,
        private RelationService $relationService
    ) {}

    public function getDefault(): DataTableFilters
    {
        return (new DataTableFilters)->setLangCode($this->dataTableLanguageResolver->resolve());
    }

    public function filter(DataTable $dataTable, DataTableFilters $filters, QueryBuilder $builder): void
    {
        if ($filters->getSearch()) {
            $this->filterSearch($dataTable, $filters, $builder);
        }

        if ($filters->getSort() && $filters->getSortDirection()) {
            $this->sort($builder, $filters);
        }

        if ($filters->getParentId()) {
            $this->filterParent($dataTable, $filters, $builder);
        }
    }

    public function filterSearch(DataTable $dataTable, DataTableFilters $filters, QueryBuilder $builder): void
    {
        foreach ($dataTable->getSearchColumns() as $column) {
            if ($this->pathService->isPath($column)) {
                list($column, $extract) = $this->pathService->toJson($column, $filters->getLangCode());

                $builder->andWhere("LOWER(JSON_UNQUOTE(JSON_EXTRACT(e.$column, '$extract'))) LIKE :search");
            } else {
                $builder->andWhere("LOWER(e.$column) LIKE :search");
            }
        }

        $builder->setParameter('search', '%' . strtolower($filters->getSearch()) . '%');
    }

    private function sort(QueryBuilder $builder, DataTableFilters $filters): void
    {
        $column = $filters->getSort();
        $order  = DataTableConfig::SORT_MAP_SQL[$filters->getSortDirection()];

        if ($this->pathService->isPath($column)) {
            list($column, $extract) = $this->pathService->toJson($column, $filters->getLangCode());

            $builder->orderBy("JSON_UNQUOTE(JSON_EXTRACT(e.$column, '$extract'))", $order);
        } else {
            $builder->orderBy("e.$column", $order);
        }
    }

    private function filterParent(DataTable $dataTable, DataTableFilters $filters, QueryBuilder $builder): void
    {
        $parentModel = $filters->getParentDataTable()->getPdoModel();
        $childModel  = $dataTable->getPdoModel();

        if ($field = $this->relationService->getOneToManyRelationField($parentModel, $childModel)) {
            $builder->andWhere("e.$field = :parentId")
                ->setParameter('parentId', $filters->getParentId());
        }
    }
}