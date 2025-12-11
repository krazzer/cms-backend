<?php

namespace App\Domain\DataTable\Dto;

use App\Domain\DataTable\Tree\RearrangeLocation;

class RearrangeDto extends Dto
{
    public int $source;
    public int $target;
    public RearrangeLocation $location;

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
}