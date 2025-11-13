<?php

namespace App\Domain\DataTable\Tree;

use App\Domain\DataTable\DataTable;

class RearrangeService
{
    public function rearrange(DataTable $dataTable, int $getSourceId, int $getTargetId, RearrangeLocation $location): void
    {
        dlog([$getSourceId, $getTargetId, $location]);
    }
}