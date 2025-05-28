<?php

namespace App\Domain\Login;

class EmailDto
{
    public string $email;

    public function getEmail(): string
    {
        return $this->email;
    }
}