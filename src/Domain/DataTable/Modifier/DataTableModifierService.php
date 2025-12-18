<?php

namespace KikCMS\Domain\DataTable\Modifier;

use KikCMS\Domain\DataTable\DataTable;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class DataTableModifierService
{
    public function __construct(
        #[TaggedIterator('datatable.modifier')]
        private iterable $modifiers
    ) {}

    public function resolve(DataTable $dataTable, string $interface): ?callable
    {
        foreach ($this->modifiers as $modifier) {
            if ($modifier instanceof $interface && $this->supports($modifier, $dataTable)) {
                return [$modifier, 'modify'];
            }
        }

        return null;

    }

    private function supports(object $modifier, DataTable $dataTable): bool
    {
        $entity       = (new ReflectionClass($dataTable->getPdoModel()))->getShortName();
        $modifierName = (new ReflectionClass($modifier))->getShortName();

        return str_starts_with($modifierName, $entity);
    }
}