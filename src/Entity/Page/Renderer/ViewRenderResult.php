<?php

namespace KikCMS\Entity\Page\Renderer;

class ViewRenderResult extends RenderResult
{
    public function __construct(public ?string $template = null, public array $context = [])
    {
        parent::__construct(RenderType::VIEW, $template, $context);
    }
}