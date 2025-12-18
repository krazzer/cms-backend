<?php

namespace KikCMS\Domain\DataTable\Dto;

class AddDto extends Dto
{
    public ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }
}