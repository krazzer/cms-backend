<?php

namespace App\Controller;

use App\Entity\Login\EmailDto;
use App\Entity\User\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    /** @var MailerInterface */
    private MailerInterface $mailer;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /**
     * @param MailerInterface $mailer
     * @param UserRepository $userRepository
     */
    public function __construct(MailerInterface $mailer, UserRepository $userRepository)
    {
        $this->mailer         = $mailer;
        $this->userRepository = $userRepository;
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

        $email = (new Email())
            ->from('hello@example.com')
            ->to($email->getEmail())
            ->subject('Time for Symfony Mailer!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        try {
            $this->mailer->send($email);
        } catch (Exception) {
            return new JsonResponse(['success' => false]);
        }

        return new JsonResponse(['success' => true]);
    }
}