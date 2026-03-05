<?php

namespace KikCMS\Domain\App\Denormalizer;

use Doctrine\ORM\EntityManagerInterface;
use KikCMS\Domain\App\Exception\ObjectNotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class EntityDenormalizer implements DenormalizerInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        $isIdOrNull = is_numeric($data) || $data === '' || $data === 'null';

        return class_exists($type) && $isIdOrNull && method_exists($type, 'getId');
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?object
    {
        if ( ! ($id = (int) $data)) {
            return null;
        }

        if ( ! $entity = $this->em->getRepository($type)->find($id)) {
            throw new ObjectNotFoundHttpException(sprintf('Object "%s" with id "%d" not found', $type, $id));
        }

        return $entity;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => false];
    }
}