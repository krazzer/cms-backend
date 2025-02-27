<?php

namespace App\Controller;

use App\Entity\Login\EmailDto;
use App\Entity\Login\PasswordResetService;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class LoginController extends AbstractController
{
    /** @var NativePasswordHasher */
    private NativePasswordHasher $passwordHasher;

    /** @var TagAwareAdapterInterface|TagAwareCacheInterface */
    private TagAwareCacheInterface|TagAwareAdapterInterface $keyValueStore;

    /** @var PasswordResetService */
    private PasswordResetService $passwordResetService;

    /**
     * @param NativePasswordHasher $passwordHasher
     * @param TagAwareCacheInterface $keyValueStore
     * @param PasswordResetService $passwordResetService
     */
    public function __construct(NativePasswordHasher $passwordHasher, TagAwareCacheInterface $keyValueStore,
        PasswordResetService $passwordResetService
    )
    {
        $this->passwordHasher       = $passwordHasher;
        $this->keyValueStore        = $keyValueStore;
        $this->passwordResetService = $passwordResetService;
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

    #[Route('/reset/{user}/{id}/{key}', name: 'reset')]
    public function reset(User $user, string $id, string $key): Response
    {
        /** @var ItemInterface $key */
        $hash = $this->keyValueStore->getItem('user_' . $user->getId() . '_' . $id);

        if ( ! $hash->isHit()) {
            // todo: expired
            return new Response('No reset key found for user ' . $user->getId());
        }

        $isValid = $this->passwordHasher->verify($hash->get(), $key);

        // todo: success
        return new Response('Reset password for user ' . $user->getId() . ' with key ' . $key . ' => ' .
            ($isValid ? 'valid' : 'invalid'));
    }
}