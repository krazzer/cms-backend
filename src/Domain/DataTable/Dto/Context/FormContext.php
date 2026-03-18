<?php

namespace KikCMS\Domain\DataTable\Dto\Context;

use KikCMS\Domain\App\Config\Provider\Context;
use KikCMS\Domain\DataTable\Config\DataTableConfig;

class FormContext extends Context
{
    const string TYPE = 'type';
    const string ID   = DataTableConfig::ID;

    private ?string $trigger;
    private array $data;

    public function __construct(array $data = [], ?string $trigger = null)
    {
        $this->data    = $data;
        $this->trigger = $trigger;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getValue(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function getTrigger(): ?string
    {
        return $this->trigger;
    }

    public function setTrigger(?string $trigger): static
    {
        $this->trigger = $trigger;
        return $this;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->getValue(self::ID);
    }

    public function getType(): ?string
    {
        return $this->getValue(self::TYPE);
    }
}