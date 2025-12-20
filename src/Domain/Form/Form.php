<?php

namespace KikCMS\Domain\Form;

class Form
{
    private array $tabs = [];
    private array $fields = [];

    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function setTabs(array $tabs): Form
    {
        $this->tabs = $tabs;
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): Form
    {
        $this->fields = $fields;
        return $this;
    }
}