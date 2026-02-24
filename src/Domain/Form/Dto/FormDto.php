<?php

namespace KikCMS\Domain\Form\Dto;

class FormDto
{
    public string $instance;

    public function getInstance(): string
    {
        return $this->instance;
    }
}