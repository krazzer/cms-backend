<?php

namespace App\Entity\Login;

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