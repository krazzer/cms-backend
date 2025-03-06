<?php

namespace App\Controller;

use App\Entity\Login\EmailDto;
use App\Entity\Login\PasswordResetService;
use App\Entity\Login\SetPasswordDto;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Routing\Annotation\Route;
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

    /**
     * @param NativePasswordHasher $passwordHasher
     * @param TagAwareCacheInterface $keyValueStore
     * @param PasswordResetService $passwordResetService
     * @param TranslatorInterface $translator
     */
    public function __construct(NativePasswordHasher $passwordHasher, TagAwareCacheInterface $keyValueStore,
        PasswordResetService $passwordResetService, TranslatorInterface $translator
    )
    {
        $this->passwordHasher       = $passwordHasher;
        $this->keyValueStore        = $keyValueStore;
        $this->passwordResetService = $passwordResetService;
        $this->translator           = $translator;
    }

    #[Route('/api/login')]
    public function login(): Response
    {
        return new JsonResponse(['success' => false]);
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

        return new JsonResponse(['success' => true]);
    }
}