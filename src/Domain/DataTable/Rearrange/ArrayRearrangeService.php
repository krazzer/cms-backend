<?php

namespace KikCMS\Domain\DataTable\Rearrange;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation as Location;

class ArrayRearrangeService
{
    public function rearrange(int $source, int $target, Location $location, array $data): array
    {
        $ids  = array_column($data, DataTableConfig::ID);
        $from = array_search($source, $ids);
        $to   = array_search($target, $ids);

        $item = $data[$from];

        unset($data[$from]);

        $data = array_values($data);

        if ($location === RearrangeLocation::AFTER) {
            $to++;
        }

        array_splice($data, $to, 0, [$item]);

        return $data;
    }
}