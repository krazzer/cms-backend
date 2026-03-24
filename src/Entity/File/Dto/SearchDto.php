<?php

namespace KikCMS\Entity\File\Dto;

class SearchDto
{
    public ?string $search = null;

    public function getSearch(): ?string
    {
        return $this->search;
    }
}