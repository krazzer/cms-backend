<?php

namespace KikCMS\Domain\DataTable\Dto;

class DeleteDto extends FilterDto
{
    public array $ids;
    public bool $confirmed = false;

    public function getIds(): array
    {
        return $this->ids;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }
}