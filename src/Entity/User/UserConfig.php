<?php

namespace App\Entity\User;

class UserConfig
{
    const string ROLE_DEV    = 'dev';
    const string ROLE_ADMIN  = 'admin';
    const string ROLE_USER   = 'user';
    const string ROLE_CLIENT = 'client';

    const array ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_DEV,
        self::ROLE_USER,
        self::ROLE_CLIENT,
    ];
}