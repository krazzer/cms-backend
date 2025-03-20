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

        $menu = [
            'pages'   => ['label' => "Pages", 'icon' => 'view-grid'],
            'clients' => ['label' => "Clients", 'icon' => 'account-multiple-outline'],
        ];

        return new JsonResponse([
            'loggedIn'         => $loggedIn,
            'menu'             => $menu,
            'html'             => 'Pages',
            'selectedMenuItem' => 'pages',
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

    #[Route('/api/module/clients')]
    public function moduleModule(): Response
    {
        $data = [
            'buttons'       => [
                ['label' => 'Add client', 'action' => 'add'],
                ['label' => 'Delete', 'action' => 'delete'],
            ],
            'headers'       => [
                'id'       => 'Id',
                'name'     => 'Name',
                'address'  => 'Address',
                'zip'      => 'Zip',
                'category' => 'Category',
            ],
            'mobileColumns' => ['id', 'name'],
            'data'          => [
                ['id' => 'a1', 'data' => ['a1', 'Peter', 'Peterstreet 17', '12345', 1]],
                ['id' => 'a2', 'data' => ['a2', 'John', 'Johnstreet 17', '23456', 2]],
                ['id' => 'a3', 'data' => ['a3', 'Susan', 'Susanstreet 17', '34567', 3]],
                ['id' => 'a4', 'data' => ['a4', 'Henry', 'Henrystreet 17', '45678', 4]],
                ['id' => 'a5', 'data' => ['a5', 'Naomi', 'Naomistreet 17', '56789', 1]],
            ],
            'instance'      => 'clients',
        ];

        $fields = [
            [
                'key' => 'group1',
                'type' => 'group',
                'size' => ['md' => 6],
                'fields' => [
                    [
                        'key' => 'firstname',
                        'type' => 'text',
                        'label' => 'Legal first name',
                        'size' => ['sm' => 6, 'md' => 4],
                        'hint' => 'example of helper text only on focus',
                        'validator' => ['name' => 'presence', 'parameters' => []]
                    ],
                    [
                        'key' => 'middlename',
                        'type' => 'text',
                        'label' => 'Legal middle name',
                        'size' => ['sm' => 6, 'md' => 4],
                        'hint' => 'example of helper text only on focus 2'
                    ],
                    [
                        'key' => 'lastname',
                        'type' => 'text',
                        'label' => 'Legal last name',
                        'size' => ['sm' => 6, 'md' => 4],
                        'hint' => 'example of helper text only on focus 3'
                    ],
                    [
                        'key' => 'email',
                        'type' => 'text',
                        'label' => 'E-mail address',
                        'validator' => ['name' => 'email', 'parameters' => ['required' => true]]
                    ],
                    [
                        'key' => 'password',
                        'type' => 'text',
                        'label' => 'Password',
                    ],
                ]
            ],
            [
                'key' => 'group2',
                'type' => 'group',
                'size' => ['md' => 6],
                'fields' => [
                    [
                        'key' => 'zip',
                        'type' => 'text',
                        'label' => 'Zip code',
                        'validator' => ['name' => 'server', 'parameters' => ['name' => 'postalcode']]
                    ],
                    [
                        'key' => 'age',
                        'type' => 'select',
                        'label' => 'Age',
                        'items' => [
                            ['key' => '0-17', 'value' => '0-17'],
                            ['key' => '18-29', 'value' => '18-29'],
                            ['key' => '30-54', 'value' => '30-54'],
                            ['key' => '54+', 'value' => '54+']
                        ],
                        'size' => ['sm' => 6],
                    ],
                    [
                        'key' => 'interests',
                        'type' => 'autocomplete',
                        'multiple' => true,
                        'label' => 'Interests',
                        'items' => [
                            ['key' => '1', 'value' => 'Skiing'],
                            ['key' => '2', 'value' => 'Ice hockey'],
                            ['key' => '3', 'value' => 'Soccer'],
                            ['key' => '4', 'value' => 'Basketball'],
                            ['key' => '5', 'value' => 'Hockey'],
                            ['key' => '6', 'value' => 'Reading'],
                            ['key' => '7', 'value' => 'Writing'],
                            ['key' => '8', 'value' => 'Coding'],
                            ['key' => '9', 'value' => 'Basejump'],
                        ],
                        'size' => ['sm' => 6],
                    ],
                ],
            ],
        ];

        return new JsonResponse([
            'dataTable'        => $data,
            'selectedMenuItem' => 'module',
        ]);
    }
}