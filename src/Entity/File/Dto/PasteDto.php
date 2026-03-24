<?php

namespace KikCMS\Entity\File\Dto;

class PasteDto
{
    public array $ids = [];
    public ?int $folder = null;

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getFolder(): ?int
    {
        return $this->folder;
    }
}