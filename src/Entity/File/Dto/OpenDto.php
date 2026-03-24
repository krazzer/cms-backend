<?php

namespace KikCMS\Entity\File\Dto;

class OpenDto
{
    public ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}