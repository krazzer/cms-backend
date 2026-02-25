<?php

namespace KikCMS\Domain\App;

use KikCMS\Domain\DataTable\DataTableService;
use KikCMS\Domain\Form\FormService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly DataTableService $dataTableService,
        private readonly FormService $formService
    ) {}

    #[Route('/api/home')]
    public function home(): Response
    {
        $loggedIn = (bool) $this->security->getUser();

        $menu = [
            'pages'    => ['label' => "Pages", 'icon' => 'view-grid'],
            'users'    => ['label' => "Users", 'icon' => 'account-multiple-outline'],
            'media'    => ['label' => "Media", 'icon' => 'image-outline'],
            'settings' => ['label' => "Settings"],
        ];

        return new JsonResponse([
            'loggedIn' => $loggedIn,
            'menu'     => $menu,
        ]);
    }

    #[Route('/api/default-module')]
    public function defaultModule(): Response
    {
        return $this->pageModule();
    }

    #[Route('/api/module/pages')]
    public function pageModule(): Response
    {
        return new JsonResponse([
            'dataTable'        => $this->dataTableService->getPayloadByInstance('pages'),
            'selectedMenuItem' => 'pages',
        ]);
    }

    #[Route('/api/module/users')]
    public function moduleModule(): Response
    {
        return new JsonResponse([
            'dataTable'        => $this->dataTableService->getPayloadByInstance('users'),
            'selectedMenuItem' => 'users',
        ]);
    }

    #[Route('/api/module/media')]
    public function mediaModule(): Response
    {
        return new JsonResponse([
            'media'            => ['files' => ['media']],
            'selectedMenuItem' => 'media',
        ]);
    }

    #[Route('/api/module/settings')]
    public function settingsModule(): Response
    {
        return new JsonResponse([
            'form'             => $this->formService->getPayloadByName('settings'),
            'selectedMenuItem' => 'settings',
        ]);
    }
}