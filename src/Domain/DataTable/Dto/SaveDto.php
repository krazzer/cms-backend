<?php

namespace KikCMS\Domain\DataTable\Dto;

class SaveDto extends FilterDto
{
    public string|null|int $id;
    public array $formData;

    public function getId(): string|null|int
    {
        return $this->id;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }
}