<?php

namespace KikCMS\Domain\DataTable;

use KikCMS\Domain\DataTable\Config\DataTableConfig;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class DataTableLanguageResolver
{
    public function __construct(
        private RequestStack $requestStack,
        private ParameterBagInterface $params
    ) {}

    public function resolve(?string $providedLangCode = null): string
    {
        if ($providedLangCode) {
            return $providedLangCode;
        }

        return $this->requestStack->getSession()->get(DataTableConfig::SESSION_KEY_LANG)
            ?? $this->params->get('app.default_cms_content_language');
    }
}
