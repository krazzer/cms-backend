<?php

namespace App\Controller;

use App\Domain\DataTable\DataTableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    /** @var Security */
    private Security $security;

    /** @var DataTableService */
    private DataTableService $dataTableService;

    /**
     * @param Security $security
     * @param DataTableService $dataTableService
     */
    public function __construct(Security $security, DataTableService $dataTableService)
    {
        $this->security         = $security;
        $this->dataTableService = $dataTableService;
    }

    #[Route('/api/home')]
    public function home(): Response
    {
        $loggedIn = (bool) $this->security->getUser();

        $menu = [
            'pages' => ['label' => "Pages", 'icon' => 'view-grid'],
            'users' => ['label' => "Users", 'icon' => 'account-multiple-outline'],
            'fail'  => ['label' => "Fail"],
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
            'dataTable'        => $this->dataTableService->getFullConfig('pages'),
            'selectedMenuItem' => 'pages',
        ]);
    }

    #[Route('/api/module/users')]
    public function moduleModule(): Response
    {
        return new JsonResponse([
            'dataTable'        => $this->dataTableService->getFullConfig('users'),
            'selectedMenuItem' => 'users',
        ]);
    }
}