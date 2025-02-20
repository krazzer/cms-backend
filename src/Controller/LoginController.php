<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/api/login')]
    public function login(): Response
    {
        return new JsonResponse(['success' => false]);
    }

    #[Route('/api/reset/send', methods: 'POST', format: 'json')]
    public function sendResetUrl(Request $request): Response
    {
        print_r(Kernel::VERSION);
        print_r($request->toArray());

        return new JsonResponse(['success' => false]);
    }
}