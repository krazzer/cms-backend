<?php

namespace KikCMS\Domain\DataTable\Dto;

use KikCMS\Domain\DataTable\Rearrange\RearrangeLocation;

class RearrangeDto extends FilterDto
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