<?php

namespace KikCMS\Doctrine\Service;

use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Domain\DataTable\Config\DataTableConfig;

readonly class EntityService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getByIds(string $model, array $ids): array
    {
        return $this->entityManager->getRepository($model)->findBy([DataTableConfig::ID => $ids]);
    }
}