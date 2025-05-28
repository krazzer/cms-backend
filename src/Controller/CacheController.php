<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheController extends AbstractController
{
    public function __construct(private readonly TagAwareCacheInterface $keyValueStore) {}

    #[Route('/cache/prune')]
    public function prune(): JsonResponse
    {
        $success = $this->keyValueStore->prune();

        return new JsonResponse(['success' => $success]);
    }
}