<?php

namespace KikCMS\Domain\Form\Dto\Denormalizer;


use KikCMS\Domain\Form\Form;
use KikCMS\Domain\Form\FormService;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class FormDenormalizer implements DenormalizerInterface
{
    public function __construct(private FormService $formService) {}

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Form::class && is_string($data);
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Form
    {
        return $this->formService->getByName($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Form::class => true];
    }
}