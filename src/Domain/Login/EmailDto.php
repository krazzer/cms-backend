<?php

namespace KikCMS\Domain\Login;

class EmailDto
{
    public string $email;

    public function getEmail(): string
    {
        return $this->email;
    }
}