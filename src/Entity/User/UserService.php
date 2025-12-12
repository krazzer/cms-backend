<?php

namespace KikCMS\Entity\User;

use KikCMS\Domain\Login\SetPasswordDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Autoconfigure(public: true)]
readonly class UserService
{
    public function __construct(
        private Security $security,
        private NativePasswordHasher $passwordHasher,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private TranslatorInterface $translator
    ) {}

    public function updatePasswordAndLogin(SetPasswordDto $dto): void
    {
        $userId = $dto->getUserId();
        $user   = $this->userRepository->find($userId);

        $user->setPassword($this->passwordHasher->hash($dto->getPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->security->login($user);
    }

    public function getRoleMap(): array
    {
        $roles = UserConfig::ROLES;

        $roleMap = [];

        foreach ($roles as $role) {
            $roleMap[$role] = $this->translator->trans('roles.' . $role);
        }

        return $roleMap;
    }
}