<?php

namespace KikCMS\Domain\DataTable\Dto;

class EditDto extends FilterDto
{
    public int $id;

    public function getId(): int
    {
        return $this->id;
    }
}