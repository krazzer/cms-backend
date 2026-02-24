<?php

namespace KikCMS\Domain\Form\Dto;

class SaveDto extends FormDto
{
    public array $data;

    public function getData(): array
    {
        return $this->data;
    }
}