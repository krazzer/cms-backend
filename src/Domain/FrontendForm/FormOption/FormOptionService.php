<?php

namespace KikCMS\Domain\FrontendForm\FormOption;

use KikCMS\Domain\App\Config\ConfigService;

readonly class FormOptionService
{
    public function __construct(
        private ConfigService $configService,
    ) {}

    public function getConfig(): array
    {
        return $this->configService->getMerged('forms');
    }

    public function getClass(string $key): ?string
    {
        return $this->getConfig()[$key]['class'] ?? null;
    }

    public function getNameMap(): array
    {
        return array_map(fn($template) => $template['label'], $this->getConfig());
    }
}