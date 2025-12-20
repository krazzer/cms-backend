<?php

namespace KikCMS\Domain\Form;

class Tab
{
    private string $key;
    private string $name;
    private array $fields = [];

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): Tab
    {
        $this->fields = $fields;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): Tab
    {
        $this->key = $key;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Tab
    {
        $this->name = $name;
        return $this;
    }
}