<?php

namespace App\Entity\User;

use App\Domain\Login\SetPasswordDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Autoconfigure(public: true)]
class UserService
{
    /** @var Security */
    private Security $security;

    /** @var NativePasswordHasher */
    private NativePasswordHasher $passwordHasher;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var TranslatorInterface */
    private TranslatorInterface $translator;

    /**
     * @param Security $security
     * @param NativePasswordHasher $passwordHasher
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(Security $security, NativePasswordHasher $passwordHasher,
        EntityManagerInterface $entityManager, UserRepository $userRepository, TranslatorInterface $translator)
    {

        $this->security       = $security;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager  = $entityManager;
        $this->userRepository = $userRepository;
        $this->translator     = $translator;
    }

    /**
     * @param SetPasswordDto $dto
     * @return void
     */
    public function updatePasswordAndLogin(SetPasswordDto $dto): void
    {
        $userId = $dto->getUserId();
        $user   = $this->userRepository->find($userId);

        $user->setPassword($this->passwordHasher->hash($dto->getPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->security->login($user);
    }

    /**
     * @return array
     */
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