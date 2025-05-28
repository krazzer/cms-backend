<?php

namespace App\Domain\Login;

class SetPasswordDto
{
    public string $password;
    public int $userId;
    public string $id;
    public string $key;

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
