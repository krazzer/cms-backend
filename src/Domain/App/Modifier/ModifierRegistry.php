<?php

namespace KikCMS\Domain\App\Modifier;

use InvalidArgumentException;
use KikCMS\Domain\DataTable\DataTable;
use KikCMS\Domain\DataTable\Filter\DataTableFilters;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class ModifierRegistry
{
    public function __construct(
        #[AutowireIterator('kikcms.modifier', indexAttribute: 'key')] private iterable $modifiers
    ) {}

    public function get(string $key): ModifierInterface
    {
        if ($provider = iterator_to_array($this->modifiers)[$key] ?? null) {
            return $provider;
        }

        throw new InvalidArgumentException("No modifier found for key: '$key'");
    }

    public function modify(string $key, mixed $object, DataTable $dataTable, DataTableFilters $filters): mixed
    {
        return $this->get($key)->modify($object, $dataTable, $filters);
    }
}