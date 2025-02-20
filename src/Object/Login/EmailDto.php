<?php

namespace App\Object\Login;

class EmailDto
{
    /** @var string */
    public string $email;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}