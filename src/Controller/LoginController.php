<?php

namespace App\Controller;

use App\Entity\Login\EmailDto;
use App\Entity\Login\PasswordResetService;
use App\Entity\Login\SetPasswordDto;
use App\Entity\User\UserService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginController extends AbstractController
{
    /** @var PasswordResetService */
    private PasswordResetService $passwordResetService;

    /** @var Security */
    private Security $security;

    /** @var UserService */
    private UserService $userService;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param PasswordResetService $passwordResetService
     * @param Security $security
     * @param UserService $userService
     * @param LoggerInterface $logger
     */
    public function __construct(PasswordResetService $passwordResetService, Security $security,
        UserService $userService, LoggerInterface $logger
    )
    {
        $this->passwordResetService = $passwordResetService;
        $this->security             = $security;
        $this->userService          = $userService;
        $this->logger               = $logger;
    }

    #[Route('/api/login', name: 'login')]
    public function login(): Response
    {
        throw new AuthenticationException('This route is handled by the LoginAuthenticator');
    }

    #[Route('/api/logout', name: 'logout')]
    public function logout(): Response
    {
        if ($this->security->getUser()) {
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
        if ($message = $this->passwordResetService->checkHashValidity($dto)) {
            return new JsonResponse(['success' => false, 'message' => $message]);
        }

        try {
            $this->userService->updatePasswordAndLogin($dto);
            $success = true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $success = false;
        }

        return new JsonResponse(['success' => $success]);
    }
}