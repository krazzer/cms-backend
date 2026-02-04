<?php

namespace KikCMS\Domain\App\Service;

class StringService
{
    public function getGetter(string $field): string
    {
        return 'get' . ucfirst($this->snakeToCamel($field));
    }

    public function getSetter(string $field): string
    {
        return 'set' . ucfirst($this->snakeToCamel($field));
    }

    public function getIsGetter(string $field): string
    {
        return 'is' . ucfirst($this->snakeToCamel($field));
    }

    function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
}