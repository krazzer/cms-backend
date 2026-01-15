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

    public function resolve(DataTable $dataTable, string $interface): ?DataTableModifierInterface
    {
        foreach ($this->modifiers as $modifier) {
            if ($modifier instanceof $interface && $this->supports($modifier, $dataTable)) {
                return $modifier;
            }
        }

        return null;

    }

    private function supports(object $modifier, DataTable $dataTable): bool
    {
        if( ! $dataTable->getPdoModel()){
            return false;
        }

        $entity       = new ReflectionClass($dataTable->getPdoModel())->getShortName();
        $modifierName = new ReflectionClass($modifier)->getShortName();

        return str_starts_with($modifierName, $entity);
    }
}