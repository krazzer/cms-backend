<?php

namespace App\Domain\DataTable\Filter;


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
    }

    public function filterSearch(DataTable $dataTable, string $search, QueryBuilder $builder): void
    {
        foreach ($dataTable->getSearchColumns() as $column) {
            if ($this->pathService->isPath($column)) {
                list($column, $extract) = $this->pathService->toJson($column, $dataTable->getLangCode());

                $builder->andWhere("LOWER(JSON_UNQUOTE(JSON_EXTRACT(e.$column, '$extract'))) LIKE :search");
            } else {
                $builder->andWhere('LOWER(e.$column) LIKE :search');
            }
        }

        $builder->setParameter('search', '%' . strtolower($search) . '%');
    }
}