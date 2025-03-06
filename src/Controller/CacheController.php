<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheController extends AbstractController
{
    /** @var TagAwareCacheInterface|TagAwareAdapterInterface */
    private TagAwareCacheInterface|TagAwareAdapterInterface $keyValueStore;

    /**
     * @param TagAwareCacheInterface $keyValueStore
     */
    public function __construct(TagAwareCacheInterface $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
    }

    #[Route('/cache/prune')]
    public function prune(): JsonResponse
    {
        $success = $this->keyValueStore->prune();

        return new JsonResponse(['success' => $success]);
    }
}