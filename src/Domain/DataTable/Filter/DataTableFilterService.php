<?php

namespace App\Domain\DataTable\Filter;


use App\Domain\DataTable\Config\DataTableConfig;
use App\Domain\DataTable\Config\DataTablePathService;
use App\Domain\DataTable\DataTable;
use Doctrine\ORM\QueryBuilder;

readonly class DataTableFilterService
{
    public function __construct(
        private DataTablePathService $pathService
    ) {}

    public function filter(DataTable $dataTable, DataTableFilters $filters, QueryBuilder $builder): void
    {
        if ($search = $filters->getSearch()) {
            $this->filterSearch($dataTable, $search, $builder);
        }

        if ($filters->getSort() && $filters->getSortDirection()) {
            $this->sort($dataTable, $builder, $filters->getSort(), $filters->getSortDirection());
        }
    }

    public function filterSearch(DataTable $dataTable, string $search, QueryBuilder $builder): void
    {
        foreach ($dataTable->getSearchColumns() as $column) {
            if ($this->pathService->isPath($column)) {
                list($column, $extract) = $this->pathService->toJson($column, $dataTable->getLangCode());

                $builder->andWhere("LOWER(JSON_UNQUOTE(JSON_EXTRACT(e.$column, '$extract'))) LIKE :search");
            } else {
                $builder->andWhere("LOWER(e.$column) LIKE :search");
            }
        }

        $builder->setParameter('search', '%' . strtolower($search) . '%');
    }

    private function sort(DataTable $dataTable, QueryBuilder $builder, string $column, string $sortDirection): void
    {
        $sortDirection = DataTableConfig::SORT_MAP_SQL[$sortDirection];

        if ($this->pathService->isPath($column)) {
            list($column, $extract) = $this->pathService->toJson($column, $dataTable->getLangCode());

            $builder->orderBy("JSON_UNQUOTE(JSON_EXTRACT(e.$column, '$extract'))", $sortDirection);
        } else {
            $builder->orderBy("e.$column", $sortDirection);
        }
    }

}