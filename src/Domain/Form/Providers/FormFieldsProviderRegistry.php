<?php

namespace KikCMS\Domain\Form\Providers;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class FormFieldsProviderRegistry
{
    public function __construct(
        #[AutowireIterator('kikcms.form_fields_provider', indexAttribute: 'key')] private iterable $providers
    ) {}

    public function get(string $key): FormFieldsProviderInterface
    {
        if ($provider = iterator_to_array($this->providers)[$key] ?? null) {
            return $provider;
        }

        throw new InvalidArgumentException("No form fields provider found for key: '$key'");
    }
}