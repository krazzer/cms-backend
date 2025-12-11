<?php

namespace App\Domain\DataTable\Dto;

class DeleteDto extends Dto
{
    public array $ids;

    public function getIds(): array
    {
        return $this->ids;
    }
}