<?php

namespace KikCMS\Entity\User;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('user_roles')]
readonly class UserRolesProvider implements ConfigProviderInterface
{
    public function __construct(private UserService $userService) {}

    public function getConfig(): array
    {
        return $this->userService->getRoleMap();
    }
}