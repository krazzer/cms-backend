<?php

namespace KikCMS\Domain\DataTable\SourceService;

use KikCMS\Domain\App\Exception\NotImplementedException;
use KikCMS\Domain\DataTable\SourceType;

readonly class DataTableSourceServiceResolver
{
    public function __construct(
        private PdoDataTableSourceService $pdoSource,
        private LocalDataTableSourceService $localSource,
    ) {}

    public function resolve(SourceType $type): DataTableSourceServiceInterface
    {
        return match ($type) {
            SourceType::Pdo   => $this->pdoSource,
            SourceType::Local => $this->localSource,
            SourceType::Cache => throw new NotImplementedException,
        };
    }
}