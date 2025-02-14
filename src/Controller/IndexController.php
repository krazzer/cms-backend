<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController
{
    #[Route('/')]
    public function index(): Response
    {
        return new Response(
            '<html lang=""><body>This is the index. Yet to be filled.</body></html>'
        );
    }
}