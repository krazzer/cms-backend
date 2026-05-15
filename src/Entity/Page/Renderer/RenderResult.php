<?php

namespace KikCMS\Entity\Page\Renderer;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class RenderResult
{
    public function __construct(
        public RenderType $type,
        public ?string $template = null,
        public array $context = [],
        public ?Response $response = null,
    )
    {
        if ($type === RenderType::VIEW && ! $template) {
            throw new InvalidArgumentException('VIEW requires template');
        }

        if ($type === RenderType::RESPONSE && ! $response) {
            throw new InvalidArgumentException('RESPONSE requires response');
        }
    }
}