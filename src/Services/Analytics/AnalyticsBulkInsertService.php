<?php

namespace KikCMS\Services\Analytics;

use Doctrine\DBAL\Connection;

class AnalyticsBulkInsertService
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    /**
     * Perform a bulk insert using DBAL.
     */
    public function insertBulk(string $table, array $rows, bool $replace = true): void
    {
        if (empty($rows)) {
            return;
        }

        $columns      = array_keys($rows[0]);
        $values       = [];
        $placeholders = [];

        foreach ($rows as $rowIndex => $row) {
            $rowPlaceholders = [];
            foreach ($columns as $col) {
                $paramName          = 'val' . $rowIndex . '_' . $col;
                $rowPlaceholders[]  = ':' . $paramName;
                $values[$paramName] = $row[$col] ?? null;
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        $sql = ($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $placeholders);

        $this->connection->executeStatement($sql, $values);
    }
}