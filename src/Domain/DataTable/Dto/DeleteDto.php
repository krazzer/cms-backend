<?php

namespace KikCMS\Domain\DataTable\Dto;

class DeleteDto extends FilterDto
{
    public array $ids;

    public function getIds(): array
    {
        return $this->ids;
    }
}