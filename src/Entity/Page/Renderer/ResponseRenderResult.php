<?php

namespace KikCMS\Entity\Page\Renderer;

use Symfony\Component\HttpFoundation\Response;

class ResponseRenderResult extends RenderResult
{
    public function __construct(public ?Response $response = null)
    {
        parent::__construct(RenderType::RESPONSE, response: $response);
    }
}