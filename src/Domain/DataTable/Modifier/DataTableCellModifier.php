<?php

namespace KikCMS\Domain\DataTable\Modifier;

use KikCMS\Domain\DataTable\Context\DataContext;

class DataTableCellModifier
{
    public function modify(DataContext $context, string $index, callable $callable): void
    {
        $rows          = $context->getData();
        $headers       = array_keys($context->getDataTable()->getHeaders());
        $headerIndexes = array_flip($headers);

        foreach ($rows as $i => $row) {
            $rowMap = array_combine($headers, $row['data']);

            $rows[$i]['data'][$headerIndexes[$index]] = $callable($rowMap[$index], $rowMap, $context->getDataTable());
        }

        $context->setData($rows);
    }
}