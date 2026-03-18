<?php

namespace KikCMS\Domain\DataTable\Dto;

class UpdateFormDto extends Dto
{
    public array $data;
    public ?string $trigger = null;

    public function getData(): array
    {
        return $this->data;
    }

    public function getTrigger(): ?string
    {
        return $this->trigger;
    }
}