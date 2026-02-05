<?php

namespace KikCMS\Domain\Form\Field\Types;

use KikCMS\Domain\Form\Field\Field;

class DatatableField extends Field
{
    private string $instance;

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function setInstance(string $instance): DatatableField
    {
        $this->instance = $instance;
        return $this;
    }
}