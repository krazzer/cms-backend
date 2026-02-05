<?php

namespace KikCMS\Domain\Form\Field;

class Field
{
    private string $key;
    private string $label;
    private string $type;
    private string $field;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): Field
    {
        $this->key = $key;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): Field
    {
        $this->label = $label;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Field
    {
        $this->type = $type;
        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): Field
    {
        $this->field = $field;
        return $this;
    }
}