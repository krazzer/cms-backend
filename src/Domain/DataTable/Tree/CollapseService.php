<?php

namespace KikCMS\Domain\DataTable\Tree;

use KikCMS\Domain\DataTable\Dto\CollapseDto;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CollapseService
{
    public function __construct(
        private CacheItemPoolInterface $keyValueStore,
        private Security $security,
    ) {}

    public function setByDto(CollapseDto $dto): void
    {
        $cacheKey  = $this->getCacheKeyByDto($dto);
        $collapsed = $dto->getCollapsed();

        $item = $this->keyValueStore->getItem($cacheKey);
        $item->set($collapsed);
        $this->keyValueStore->save($item);
    }

    public function isCollapsed(string $id, string $instance): bool
    {
        $cacheKey = $this->getCacheKeyByInstanceAndId($instance, $id);

        return (bool) $this->keyValueStore->getItem($cacheKey)->get();
    }

    public function getCollapsedMap(array $ids, string $instance): array
    {
        $collapsedMap = [];
        $cacheKeys    = [];

        foreach ($ids as $id) {
            $cacheKey = $this->getCacheKeyByInstanceAndId($instance, $id);

            $cacheKeys[$id] = $cacheKey;
        }

        $items = $this->keyValueStore->getItems($cacheKeys);

        foreach ($items as $item) {
            $id = array_flip($cacheKeys)[$item->getKey()];

            if($item->get()) {
                $collapsedMap[$id] = $item->get();
            }
        }

        return $collapsedMap;
    }

    private function getCacheKeyByDto(CollapseDto $dto): string
    {
        return $this->getCacheKeyByInstanceAndId($dto->getDataTable()->getInstance(), $dto->getId());
    }

    private function getCacheKeyByInstanceAndId(string $instance, int $id): string
    {
        $userId = $this->security->getUser()->getId();

        return $this->getCacheKeyPrefixByInstanceAndUserId($instance, $userId) . '_' . $id;
    }

    private function getCacheKeyPrefixByInstanceAndUserId(string $instance, int $userId): string
    {
        return 'datatable_collapse_' . $instance . '_user_' . $userId;
    }
}