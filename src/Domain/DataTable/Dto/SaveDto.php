<?php

namespace App\Domain\DataTable\Dto;

class SaveDto extends Dto
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