<?php

namespace KikCMS\Domain\DataTable\Dto;

class CollapseDto extends Dto
{
    public string $id;
    public bool $collapsed;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCollapsed(): bool
    {
        return $this->collapsed;
    }
}