<?php

namespace App\Entity\Page;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
readonly class PageService
{
    public function __construct(private PageTreeService $pageTreeService) {}

    public function modifyDataTableOutput(array $data): array
    {
       return $this->pageTreeService->sort($data);
    }
}