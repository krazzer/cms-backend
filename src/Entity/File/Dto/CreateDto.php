<?php

namespace KikCMS\Entity\File\Dto;

class CreateDto
{
    public string $name;
    public ?int $folderId = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function getFolderId(): ?int
    {
        return $this->folderId;
    }
}