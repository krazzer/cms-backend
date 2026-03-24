<?php

namespace KikCMS\Entity\File\Dto;

class ChangeKeyDto
{
    public int $id;
    public string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}