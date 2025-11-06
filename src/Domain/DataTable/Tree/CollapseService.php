<?php

namespace App\Domain\DataTable\Tree;

use App\Domain\DataTable\Dto\DataTableCollapseDto;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class CollapseService
{
    public function __construct(
        private TagAwareCacheInterface $keyValueStore,
        private Security $security,
    ) {}

    public function setByDto(DataTableCollapseDto $dto): void
    {
        $cacheKey  = $this->getCacheKeyByDto($dto);
        $collapsed = $dto->getCollapsed();

        $this->keyValueStore->get($cacheKey, function (ItemInterface $item) use ($collapsed) {
            $item->expiresAfter(null);
            return $collapsed;
        });
    }

    private function getCacheKeyByDto(DataTableCollapseDto $dto): string
    {
        $userId = $this->security->getUser()->getId();

        return $this->getCacheKeyPrefixByInstanceAndUserId($dto->getInstance(), $userId) . '_' . $dto->getId();
    }

    private function getCacheKeyPrefixByInstanceAndUserId(string $instance, int $userId): string
    {
        return 'datatable_collapse_' . $instance . '_user_' . $userId;
    }
}