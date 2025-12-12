<?php

namespace KikCMS\Domain\DataTable\Dto;

class SaveDto extends FilterDto
{
    public string|null $id;
    public array $data;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }
}