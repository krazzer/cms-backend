<?php

namespace App\Controller;

use App\Entity\Login\EmailDto;
use App\Entity\User\User;
use App\Entity\User\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class LoginController extends AbstractController
{
    /** @var MailerInterface */
    private MailerInterface $mailer;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var NativePasswordHasher */
    private NativePasswordHasher $passwordHasher;

    /** @var TagAwareAdapterInterface|TagAwareCacheInterface */
    private TagAwareCacheInterface|TagAwareAdapterInterface $keyValueStore;

    /** @var ByteString */
    private ByteString $byteString;

    /**
     * @param MailerInterface $mailer
     * @param UserRepository $userRepository
     * @param NativePasswordHasher $passwordHasher
     * @param TagAwareCacheInterface $keyValueStore
     * @param ByteString $byteString
     */
    public function __construct(MailerInterface $mailer, UserRepository $userRepository,
        NativePasswordHasher $passwordHasher, TagAwareCacheInterface $keyValueStore, ByteString $byteString)
    {
        $this->mailer         = $mailer;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->keyValueStore  = $keyValueStore;
        $this->byteString     = $byteString;
    }

    #[Route('/api/login')]
    public function login(): Response
    {
        return new JsonResponse(['success' => false]);
    }

    #[Route('/api/reset/send', methods: 'POST')]
    public function sendResetUrl(#[MapRequestPayload] EmailDto $email): Response
    {
        $user = $this->userRepository->findOneBy(['email' => $email->getEmail()]);

        if ( ! $user) {
            return new JsonResponse(['success' => true]);
        }

        $id  = $this->byteString->fromRandom(4)->lower();
        $key = $this->byteString->fromRandom()->lower();

        $hash = $this->passwordHasher->hash($key);

        $cacheKey = 'user_' . $user->getId() . '_' . $id;

        $this->keyValueStore->deleteItem($cacheKey);

        $this->keyValueStore->get($cacheKey, function (ItemInterface $item) use ($hash) {
            $item->tag('reset_password');
            $item->expiresAfter(3600);

            return $hash;
        });

        $url = $this->generateUrl('reset', ['user' => $user->getId(), 'id' => $id, 'key' => $key], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('hello@example.com')
            ->to($email->getEmail())
            ->subject('Reset!')
            ->html('<p>RESET! <a href="' . $url . '">' . $url . '</a></p>');

        try {
            $this->mailer->send($email);
        } catch (Exception) {
            return new JsonResponse(['success' => false]);
        }

        return new JsonResponse(['success' => true]);
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