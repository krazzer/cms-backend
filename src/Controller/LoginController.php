<?php

namespace App\Controller;

use App\Entity\Login\EmailDto;
use App\Entity\Login\PasswordResetService;
use App\Entity\Login\SetPasswordDto;
use App\Entity\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    /** @var NativePasswordHasher */
    private NativePasswordHasher $passwordHasher;

    /** @var TagAwareAdapterInterface|TagAwareCacheInterface */
    private TagAwareCacheInterface|TagAwareAdapterInterface $keyValueStore;

    /** @var PasswordResetService */
    private PasswordResetService $passwordResetService;

    /** @var TranslatorInterface */
    private TranslatorInterface $translator;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var Security */
    private Security               $security;

    /**
     * @param NativePasswordHasher $passwordHasher
     * @param TagAwareCacheInterface $keyValueStore
     * @param PasswordResetService $passwordResetService
     * @param TranslatorInterface $translator
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     */
    public function __construct(NativePasswordHasher $passwordHasher, TagAwareCacheInterface $keyValueStore,
        PasswordResetService $passwordResetService, TranslatorInterface $translator, UserRepository $userRepository,
        EntityManagerInterface $entityManager, Security $security
    )
    {
        $this->passwordHasher       = $passwordHasher;
        $this->keyValueStore        = $keyValueStore;
        $this->passwordResetService = $passwordResetService;
        $this->translator           = $translator;
        $this->userRepository       = $userRepository;
        $this->entityManager        = $entityManager;
        $this->security = $security;
    }

    #[Route('/api/login', name: 'login')]
    public function login(): Response
    {
        throw new AuthenticationException('This route is handled by the LoginAuthenticator');
    }

    #[Route('/api/logout', name: 'logout')]
    public function logout(): Response
    {
        if($this->security->getUser()) {
            $this->security->logout(false);
            $message = 'succesfully logged out';
        } else {
            $message = 'already logged out';
        }

        return new JsonResponse(['message' => $message]);
    }

    #[Route('/api/reset/send', methods: 'POST')]
    public function sendResetUrl(#[MapRequestPayload] EmailDto $email): Response
    {
        $success = $this->passwordResetService->sendResetUrl($email);

        return new JsonResponse(['success' => $success]);
    }

    #[Route('/api/reset/setpassword', name: 'set-password')]
    public function setPassword(#[MapRequestPayload] SetPasswordDto $dto): JsonResponse
    {
        $userId = $dto->getUserId();

        /** @var ItemInterface $key */
        $hash = $this->keyValueStore->getItem($this->passwordResetService->getCacheKey($userId, $dto->getId()));

        $invalidOrExpiredMessage = $this->translator->trans('setPassword.invalidOrExpiredUrl', domain: 'login');
        $passwordTooShortMessage = $this->translator->trans('setPassword.length', domain: 'login');

        if ( ! $hash->isHit()) {
            return new JsonResponse(['success' => false, 'message' => $invalidOrExpiredMessage]);
        }

        if ( ! $this->passwordHasher->verify($hash->get(), $dto->getKey())) {
            return new JsonResponse(['success' => false, 'message' => $invalidOrExpiredMessage]);
        }

        if (strlen($dto->getPassword()) < 12) {
            return new JsonResponse(['success' => false, 'message' => $passwordTooShortMessage]);
        }

        $user = $this->userRepository->find($userId);
        $user->setPassword($this->passwordHasher->hash($dto->getPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->security->login($user);

        return new JsonResponse(['success' => true]);
    }
}