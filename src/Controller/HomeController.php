<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController
{
    #[Route('/api/home')]
    public function home(): Response
    {
        $loggedIn = false;

        return new JsonResponse(['loggedIn' => $loggedIn]);
    }
}