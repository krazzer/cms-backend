<?php

namespace KikCMS\Entity\File\Dto;

class DeleteDto
{
    public array $ids = [];
    public ?int $folderId = null;

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getFolderId(): ?int
    {
        return $this->folderId;
    }
}