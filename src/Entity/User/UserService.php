<?php

namespace App\Entity\User;

use App\Entity\Login\SetPasswordDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

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

    /**
     * @param Security $security
     * @param NativePasswordHasher $passwordHasher
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(Security $security, NativePasswordHasher $passwordHasher,
        EntityManagerInterface $entityManager, UserRepository $userRepository)
    {

        $this->security       = $security;
        $this->passwordHasher = $passwordHasher;
        $this->entityManager  = $entityManager;
        $this->userRepository = $userRepository;
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
}