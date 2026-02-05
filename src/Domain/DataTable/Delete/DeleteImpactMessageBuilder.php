<?php

namespace KikCMS\Domain\DataTable\Delete;


use Symfony\Contracts\Translation\TranslatorInterface;

readonly class DeleteImpactMessageBuilder
{
    public function __construct(private TranslatorInterface $translator) {}

    public function build(array $deletePlan): string
    {
        $relations = [];

        foreach ($deletePlan as $class => $count) {
            $relations[] = "➜ " . $this->getEntityName($class) . ": " . $count . "x";
        }

        return $this->translator->trans("dataTable.deleteRelations", ['relations' => implode("\n", $relations)]);
    }

    private function getEntityName(string $class): string
    {
        $classParts = explode('\\', $class);
        $classBase  = array_pop($classParts);

        $transKey = "entityName." . $classBase;

        $trans = $this->translator->trans($transKey);

        if ($trans !== $transKey) {
            return $trans;
        }

        return $classBase;
    }
}