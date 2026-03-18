<?php

namespace KikCMS\Entity\User;

use KikCMS\Domain\App\Config\Provider\ConfigProviderInterface;
use KikCMS\Domain\App\Config\Provider\Context;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('user_roles')]
readonly class UserRolesProvider implements ConfigProviderInterface
{
    public function __construct(private UserService $userService) {}

    public function getConfig(Context $context): array
    {
        return $this->userService->getRoleMap();
    }
}