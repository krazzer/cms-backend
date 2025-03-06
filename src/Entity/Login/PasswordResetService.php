<?php

namespace App\Entity\Login;

use App\Entity\Mail\CmsMailer;
use App\Entity\User\User;
use App\Entity\User\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordResetService
{
    private CmsMailer              $mailer;
    private UserRepository         $userRepository;
    private NativePasswordHasher   $passwordHasher;
    private TagAwareCacheInterface $keyValueStore;
    private ByteString             $byteString;
    private UrlGeneratorInterface  $router;
    private TranslatorInterface    $translator;

    /**
     * @param CmsMailer $mailer
     * @param UserRepository $userRepository
     * @param NativePasswordHasher $passwordHasher
     * @param TagAwareCacheInterface $keyValueStore
     * @param ByteString $byteString
     * @param UrlGeneratorInterface $router
     * @param TranslatorInterface $translator
     */
    public function __construct(CmsMailer $mailer, UserRepository $userRepository, NativePasswordHasher $passwordHasher,
        TagAwareCacheInterface $keyValueStore, ByteString $byteString, UrlGeneratorInterface $router,
        TranslatorInterface $translator)
    {
        $this->mailer         = $mailer;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->keyValueStore  = $keyValueStore;
        $this->byteString     = $byteString;
        $this->router         = $router;
        $this->translator     = $translator;
    }

    /**
     * @param EmailDto $email
     * @return bool
     */
    public function sendResetUrl(EmailDto $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email->getEmail()]);

        if ( ! $user) {
            return true;
        }

        $url = $this->generateResetUrl($user);

        $email = (new TemplatedEmail())
            ->to($email->getEmail())
            ->subject($this->translator->trans('resetMail.subject', domain: 'login'))
            ->htmlTemplate('email/reset.twig')
            ->context(['url' => $url]);

        return $this->mailer->send($email);
    }

    /**
     * @param int $userId
     * @param string $id
     * @return string
     */
    public function getCacheKey(int $userId, string $id): string
    {
        return 'user_' . $userId . '_' . $id;
    }

    /**
     * @param User $user
     * @return string
     */
    private function generateResetUrl(User $user): string
    {
        $id  = $this->byteString->fromRandom(4)->lower();
        $key = $this->byteString->fromRandom()->lower();

        $hash = $this->passwordHasher->hash($key);

        $cacheKey = $this->getCacheKey($user->getId(), $id);

        $this->keyValueStore->get($cacheKey, function (ItemInterface $item) use ($hash) {
            $item->tag('reset_password');
            $item->expiresAfter(3600);

            return $hash;
        });

        $urlParams = ['user' => $user->getId(), 'id' => $id, 'key' => $key];

        return $this->router->generate('reset', $urlParams, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}