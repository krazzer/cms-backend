<?php

namespace KikCMS\Entity\Page;

use KikCMS\Domain\DataTable\DataTable;

class PageDataTable extends DataTable
{
    public function getClass(): ?string
    {
        return 'pages';
    }
}