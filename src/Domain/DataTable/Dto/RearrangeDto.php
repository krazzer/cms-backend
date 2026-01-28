<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Object\DataTableStoreData;
use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation;

class RearrangeDto extends FilterDto
{
    public int $source;
    public int $target;
    public RearrangeLocation $location;
    public DataTableStoreData $storeData;

    public function getSource(): int
    {
        return $this->source;
    }

    public function getTarget(): int
    {
        return $this->target;
    }

    public function getLocation(): RearrangeLocation
    {
        return $this->location;
    }

    public function getStoreData(): DataTableStoreData
    {
        return $this->storeData;
    }
}