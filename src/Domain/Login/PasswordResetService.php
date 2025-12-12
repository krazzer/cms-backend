<?php

namespace KikCMS\Domain\Login;

use KikCMS\Domain\Mail\CmsMailer;
use KikCMS\Entity\User\User;
use KikCMS\Entity\User\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\ByteString;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class PasswordResetService
{
    public function __construct(
        private CmsMailer $mailer,
        private UserRepository $userRepository,
        private NativePasswordHasher $passwordHasher,
        private TagAwareCacheInterface $keyValueStore,
        private ByteString $byteString,
        private UrlGeneratorInterface $router,
        private TranslatorInterface $translator
    ) {}

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

    public function getCacheKey(int $userId, string $id): string
    {
        return 'user_' . $userId . '_' . $id;
    }

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

    public function checkHashValidity(SetPasswordDto $dto): ?string
    {
        $userId = $dto->getUserId();

        /** @var ItemInterface $key */
        $hash = $this->keyValueStore->getItem($this->getCacheKey($userId, $dto->getId()));

        $invalidOrExpiredMessage = $this->translator->trans('setPassword.invalidOrExpiredUrl', domain: 'login');
        $passwordTooShortMessage = $this->translator->trans('setPassword.length', domain: 'login');

        if ( ! $hash->isHit()) {
            return $invalidOrExpiredMessage;
        }

        if ( ! $this->passwordHasher->verify($hash->get(), $dto->getKey())) {
            return $invalidOrExpiredMessage;
        }

        if (strlen($dto->getPassword()) < 12) {
            return $passwordTooShortMessage;
        }

        return null;
    }
}