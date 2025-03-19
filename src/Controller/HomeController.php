<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /** @var Security */
    private Security $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/api/home')]
    public function home(): Response
    {
        $loggedIn = (bool) $this->security->getUser();

        return new JsonResponse([
            'loggedIn' => $loggedIn,
            'menu'     => [
                'pages'  => ['label' => "Pages", 'icon' => 'view-grid'],
                'module' => ['label' => "Module", 'icon' => 'view-grid'],
            ],
            'html'     => 'Pages',
        ]);
    }

    #[Route('/api/default-module')]
    public function defaultModule(): Response
    {
        return new JsonResponse([
            'html' => 'Welcome!',
        ]);
    }

    #[Route('/api/module/pages')]
    public function pageModule(): Response
    {
        return new JsonResponse([
            'html'             => 'Pages',
            'selectedMenuItem' => 'pages',
        ]);
    }

    #[Route('/api/module/module')]
    public function moduleModule(): Response
    {
        return new JsonResponse([
            'html'             => 'Module',
            'selectedMenuItem' => 'module',
        ]);
    }
}