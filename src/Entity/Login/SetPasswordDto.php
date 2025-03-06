<?php

namespace App\Entity\Login;

class SetPasswordDto
{
    /** @var string */
    public string $password;

    /** @var int */
    public int $userId;

    /** @var string */
    public string $id;

    /** @var string */
    public string $key;

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}