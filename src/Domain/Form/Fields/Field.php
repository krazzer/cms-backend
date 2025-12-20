<?php

namespace KikCMS\Domain\Form\Fields;

class Field
{
    private string $label;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): Field
    {
        $this->label = $label;
        return $this;
    }
}