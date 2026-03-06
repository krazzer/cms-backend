<?php

namespace KikCMS\Domain\App\Config\Provider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kikcms.config_provider')]
interface ConfigProviderInterface
{
    public function getConfig(): array;
}