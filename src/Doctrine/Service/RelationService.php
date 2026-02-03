<?php

namespace KikCMS\Doctrine\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use RuntimeException;

readonly class RelationService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getOneToManyRelatedField(string $parentClass, string $childClass): string
    {
        $childMeta = $this->em->getClassMetadata($childClass);

        foreach ($childMeta->associationMappings as $mapping) {
            if ($mapping['type'] === ClassMetadata::MANY_TO_ONE && $mapping['targetEntity'] === $parentClass) {
                return $mapping['fieldName'];
            }
        }

        throw new RuntimeException(sprintf('No OneToMany / ManyToOne relation found from %s to %s',
            $childClass, $parentClass));
    }
}