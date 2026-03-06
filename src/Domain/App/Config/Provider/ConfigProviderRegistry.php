<?php

namespace KikCMS\Domain\App\Config\Provider;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class ConfigProviderRegistry
{
    public function __construct(
        #[AutowireIterator('kikcms.config_provider', indexAttribute: 'key')] private iterable $providers
    ) {}

    public function get(string $key): ConfigProviderInterface
    {
        if ($provider = iterator_to_array($this->providers)[$key] ?? null) {
            return $provider;
        }

        throw new InvalidArgumentException("No provider found for key: '$key'");
    }

    public function getConfig(string $key): array
    {
        return $this->get($key)->getConfig();
    }
}