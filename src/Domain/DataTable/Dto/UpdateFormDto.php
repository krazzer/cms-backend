<?php

namespace KikCMS\Domain\DataTable\Dto;

class UpdateFormDto extends Dto
{
    public string $field;
    public mixed $value;

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue()
    {
        return $this->value;
    }
}