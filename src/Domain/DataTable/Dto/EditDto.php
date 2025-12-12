<?php

namespace KikCMS\Domain\DataTable\Dto;

class EditDto extends Dto
{
    public string $id;

    public function getId(): string
    {
        return $this->id;
    }
}