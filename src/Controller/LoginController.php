<?php

namespace App\Controller;

use App\Entity\Login\EmailDto;
use App\Entity\User\User;
use App\Entity\User\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class LoginController extends AbstractController
{
    /** @var MailerInterface */
    private MailerInterface $mailer;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var TagAwareCacheInterface */
    private TagAwareCacheInterface      $keyValueStore;

    /** @var NativePasswordHasher */
    private NativePasswordHasher $passwordHasher;

    /**
     * @param MailerInterface $mailer
     * @param UserRepository $userRepository
     * @param TagAwareCacheInterface $keyValueStore
     * @param NativePasswordHasher $passwordHasher
     */
    public function __construct(MailerInterface $mailer, UserRepository $userRepository,
        TagAwareCacheInterface $keyValueStore, NativePasswordHasher $passwordHasher)
    {
        $this->mailer         = $mailer;
        $this->userRepository = $userRepository;
        $this->keyValueStore  = $keyValueStore;
        $this->passwordHasher = $passwordHasher;
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

        $key = $this->keyValueStore->get('user_' . $user->getId(), function (ItemInterface $item) use ($user) {
            $item->tag('reset_password');
            $item->expiresAfter(3600);

            return bin2hex(random_bytes(32));
        });

        $hash = base64_encode($this->passwordHasher->hash($key));

        $url = $this->generateUrl('reset', ['user' => $user->getId(), 'hash' => $hash], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('hello@example.com')
            ->to($email->getEmail())
            ->subject('Reset!')
            ->html('<p>RESET! ' . $url . '</p>');

        try {
            $this->mailer->send($email);
        } catch (Exception) {
            return new JsonResponse(['success' => false]);
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/reset/{user}/{hash}', name: 'reset')]
    public function reset(User $user, string $hash): Response
    {
        $hash = base64_decode($hash);
        $key = $this->keyValueStore->get('user_' . $user->getId(), fn() => null);

        $isValid = $this->passwordHasher->verify($hash, $key);

        return new Response('Reset password for user ' . $user->getId() . ' with hash ' . $hash . ' => ' .
            ($isValid ? 'valid' : 'invalid'));
    }
}