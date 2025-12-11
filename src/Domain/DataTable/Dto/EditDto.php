<?php

namespace App\Domain\DataTable\Dto;

class EditDto extends Dto
{
    public string $id;

    public function getId(): string
    {
        return $this->id;
    }
}