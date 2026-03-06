<?php

namespace KikCMS\Domain\Form;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use KikCMS\Domain\Form\Config\SourceType;

class Form
{
    private SourceType $source;
    private array $tabs = [];
    private array $fields = [];
    private ?string $name = null;

    public function getTabs(): array
    {
        return $this->tabs;
    }

    public function setTabs(array $tabs): static
    {
        $this->tabs = $tabs;
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }

    public function getSource(): SourceType
    {
        return $this->source;
    }

    public function setSource(SourceType $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function setField(string $key, array $field): static
    {
        $this->fields[$key] = $field;
        return $this;
    }

    public function setTabField(string $tabKey, string $fieldKey, array $field): static
    {
        $this->tabs[$tabKey][DataTableConfig::FORM_FIELDS][$fieldKey] = $field;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }
}