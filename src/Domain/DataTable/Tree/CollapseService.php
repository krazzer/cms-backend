<?php

namespace KikCMS\Domain\DataTable\Tree;

use KikCMS\Domain\App\KeyValue\KeyValueConfig;
use KikCMS\Domain\App\KeyValue\KeyValueService;
use KikCMS\Domain\DataTable\Dto\CollapseDto;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CollapseService
{
    public function __construct(
        private KeyValueService $keyValueService,
        private Security $security,
    ) {}

    public function setByDto(CollapseDto $dto): void
    {
        $cacheKey  = $this->getCacheKeyByDto($dto);
        $collapsed = $dto->getCollapsed();

        $this->keyValueService->set($cacheKey, $collapsed);
    }

    public function isCollapsed(string $id, string $instance): bool
    {
        $cacheKey = $this->getCacheKeyByInstanceAndId($instance, $id);

        return (bool) $this->keyValueService->get($cacheKey);
    }

    private function getCacheKeyByDto(CollapseDto $dto): string
    {
        return $this->getCacheKeyByInstanceAndId($dto->getDataTable()->getInstance(), $dto->getId());
    }

    private function getCacheKeyByInstanceAndId(string $instance, int $id): string
    {
        $userId = $this->security->getUser()?->getId() ?? 0;

        return $this->getCacheKeyPrefixByInstanceAndUserId($instance, $userId) . KeyValueConfig::SEPARATOR . $id;
    }

    private function getCacheKeyPrefixByInstanceAndUserId(string $instance, int $userId): string
    {
        return implode(KeyValueConfig::SEPARATOR, ['dataTableCollapse', $instance, 'user', $userId]);
    }
}