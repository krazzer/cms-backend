<?php

namespace KikCMS\Doctrine\Service;

use Doctrine\ORM\QueryBuilder;

class ResultService
{
    public function getAssoc(QueryBuilder $builder): array
    {
        $assocResult = [];

        foreach ($builder->getQuery()->getArrayResult() as $row) {
            [$key, $value] = array_values($row);
            $assocResult[$key] = $value;
        }

        return $assocResult;
    }

    public function getExists(QueryBuilder $builder): bool
    {
        return (bool) $builder->getQuery()->getArrayResult();
    }
}